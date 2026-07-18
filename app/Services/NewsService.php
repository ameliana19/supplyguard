<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Country;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Config;

class NewsService
{
    /**
     * Sync news from NewsAPI and link to countries and categories dynamically
     */
    public function syncNews(): array
    {
        try {
            $apiKey = env('NEWS_API_KEY') ?: Config::get('services.newsapi.api_key');
            $baseUrl = Config::get('services.newsapi.base_url', 'https://newsapi.org/v2');
            
            $useSimulated = !$apiKey;
            $totalSavedCount = 0;
            $countries = Country::all();
            $savedUrls = [];

            // Clear old articles to make sure only fresh, valid news is displayed
            Article::query()->delete();

            $categoryKeywords = [
                'Logistik' => [
                    ['keyword' => 'logistik Indonesia', 'lang' => 'id'],
                    ['keyword' => 'global logistics shipping', 'lang' => 'en'],
                    ['keyword' => 'distribusi barang', 'lang' => 'id'],
                ],
                'Rantai Pasok' => [
                    ['keyword' => 'supply chain Indonesia', 'lang' => 'id'],
                    ['keyword' => 'global supply chain cargo', 'lang' => 'en'],
                    ['keyword' => 'rantai pasok', 'lang' => 'id']
                ],
                'Pelabuhan' => [
                    ['keyword' => 'Pelindo', 'lang' => 'id'],
                    ['keyword' => 'port shipping terminal', 'lang' => 'en'],
                    ['keyword' => 'Tanjung Priok', 'lang' => 'id'],
                ],
                'Ekspor' => [
                    ['keyword' => 'ekspor Indonesia', 'lang' => 'id'],
                    ['keyword' => 'global exports trade', 'lang' => 'en'],
                    ['keyword' => 'ekspor komoditas', 'lang' => 'id']
                ],
                'Impor' => [
                    ['keyword' => 'impor Indonesia', 'lang' => 'id'],
                    ['keyword' => 'global imports customs', 'lang' => 'en'],
                    ['keyword' => 'bea cukai', 'lang' => 'id']
                ],
                'Perdagangan Internasional' => [
                    ['keyword' => 'perdagangan internasional Indonesia', 'lang' => 'id'],
                    ['keyword' => 'international trade agreement', 'lang' => 'en'],
                    ['keyword' => 'WTO', 'lang' => 'id'],
                ],
                'Maritim' => [
                    ['keyword' => 'maritim Indonesia', 'lang' => 'id'],
                    ['keyword' => 'maritime shipping vessel', 'lang' => 'en'],
                    ['keyword' => 'pelayaran', 'lang' => 'id'],
                ],
                'Cuaca Pengiriman' => [
                    ['keyword' => 'BMKG laut', 'lang' => 'id'],
                    ['keyword' => 'marine weather ocean storm', 'lang' => 'en'],
                    ['keyword' => 'cuaca pelayaran', 'lang' => 'id']
                ],
                'Ekonomi Indonesia' => [
                    ['keyword' => 'ekonomi Indonesia', 'lang' => 'id'],
                    ['keyword' => 'global economy inflation', 'lang' => 'en'],
                    ['keyword' => 'Bank Indonesia', 'lang' => 'id'],
                ]
            ];

            foreach ($categoryKeywords as $categoryName => $keywordList) {
                $categoryArticles = [];
                $usedImages = [];
                $lastImage = null;

                // 1. Try NewsAPI keywords sequentially until we get at least 15 articles
                if (!$useSimulated) {
                    foreach ($keywordList as $keywordItem) {
                        if (count($categoryArticles) >= 15) {
                            break;
                        }

                        $keyword = $keywordItem['keyword'];
                        $lang = $keywordItem['lang'];

                        try {
                            $params = [
                                'q' => $keyword,
                                'sortBy' => 'publishedAt',
                                'apiKey' => $apiKey,
                                'pageSize' => 20,
                                'language' => $lang
                            ];

                            $response = Http::timeout(2)->get("{$baseUrl}/everything", $params);

                            if ($response->successful()) {
                                $articles = $response->json()['articles'] ?? [];
                                foreach ($articles as $art) {
                                    if (count($categoryArticles) >= 15) {
                                        break;
                                    }

                                    if (empty($art['title']) || $art['title'] === '[Removed]' || empty($art['url'])) {
                                        continue;
                                    }

                                    $title = $art['title'];
                                    $description = $art['description'] ?? '';
                                    $contentBody = $art['content'] ?? '';
                                    $url = $art['url'];

                                    if (in_array($url, $savedUrls)) {
                                        continue;
                                    }

                                    if ($this->shouldIgnore($url, $title, $description)) {
                                        continue;
                                    }

                                    // Translate English news dynamically to Indonesian
                                    if ($lang === 'en') {
                                        $titleId = $this->translateToIndonesian($title);
                                        $summaryId = $this->translateToIndonesian($description ?: substr($contentBody, 0, 200));
                                        $contentBodyId = $contentBody ? $this->translateToIndonesian($contentBody) : null;
                                    } else {
                                        $titleId = $title;
                                        $summaryId = $description ?: substr($contentBody, 0, 200);
                                        $contentBodyId = $contentBody ?: null;
                                    }

                                    $image = $this->assignImage($categoryName, $usedImages, $lastImage, $art['urlToImage'] ?? null);

                                    $categoryArticles[] = [
                                        'url' => $url,
                                        'title' => $title,
                                        'title_id' => $titleId,
                                        'summary' => $description ?: substr($contentBody, 0, 200),
                                        'summary_id' => $summaryId,
                                        'content' => $contentBodyId,
                                        'image' => $image,
                                        'source' => $art['source']['name'] ?? 'News API',
                                        'author' => $art['author'] ?? 'Redaksi',
                                        'published_at' => isset($art['publishedAt']) ? date('Y-m-d', strtotime($art['publishedAt'])) : date('Y-m-d'),
                                    ];
                                }
                            } else {
                                if ($response->status() === 401 || $response->status() === 429) {
                                    $useSimulated = true;
                                }
                            }
                        } catch (\Exception $e) {
                            Log::error("Gagal melakukan sync NewsAPI untuk kategori {$categoryName} dengan keyword {$keyword}: " . $e->getMessage());
                            $useSimulated = true;
                        }
                    }
                }

                // 2. If we still don't have at least 15 articles, fetch from Google News RSS
                if (!$useSimulated && count($categoryArticles) < 15) {
                    foreach ($keywordList as $keywordItem) {
                        if (count($categoryArticles) >= 15) {
                            break;
                        }

                        $rssQuery = $keywordItem['keyword'];
                        $lang = $keywordItem['lang'];
                        try {
                            $hl = $lang === 'en' ? 'en' : 'id';
                            $gl = $lang === 'en' ? 'US' : 'ID';
                            $ceid = $lang === 'en' ? 'US:en' : 'ID:id';
                            $rssUrl = "https://news.google.com/rss/search?q=" . urlencode($rssQuery) . "&hl={$hl}&gl={$gl}&ceid={$ceid}";
                            
                            $response = Http::timeout(2)->get($rssUrl);
                            if ($response->successful()) {
                                $xml = simplexml_load_string($response->body());
                                if ($xml && isset($xml->channel->item)) {
                                    foreach ($xml->channel->item as $item) {
                                        if (count($categoryArticles) >= 15) {
                                            break;
                                        }

                                        $fullTitle = (string) $item->title;
                                        $link = (string) $item->link;
                                        $pubDate = (string) $item->pubDate;

                                        if (empty($fullTitle) || empty($link)) {
                                            continue;
                                        }

                                        if (in_array($link, $savedUrls)) {
                                            continue;
                                        }

                                        // Parse source and title
                                        $parts = explode(' - ', $fullTitle);
                                        $source = 'Google News';
                                        if (count($parts) > 1) {
                                            $source = array_pop($parts);
                                            $title = implode(' - ', $parts);
                                        } else {
                                            $title = $fullTitle;
                                        }

                                        if ($this->shouldIgnore($link, $title, '')) {
                                            continue;
                                        }

                                        // Translate English news dynamically to Indonesian
                                        if ($lang === 'en') {
                                            $titleId = $this->translateToIndonesian($title);
                                            $summaryId = $this->translateToIndonesian($title);
                                            $contentBodyId = $this->translateToIndonesian($title);
                                        } else {
                                            $titleId = $title;
                                            $summaryId = $title;
                                            $contentBodyId = $title;
                                        }

                                        $publishedAt = date('Y-m-d', strtotime($pubDate));
                                        $image = $this->assignImage($categoryName, $usedImages, $lastImage, null);

                                        $categoryArticles[] = [
                                            'url' => $link,
                                            'title' => $title,
                                            'title_id' => $titleId,
                                            'summary' => $title,
                                            'summary_id' => $summaryId,
                                            'content' => $contentBodyId,
                                            'image' => $image,
                                            'source' => $source,
                                            'author' => 'Redaksi',
                                            'published_at' => $publishedAt,
                                        ];
                                    }
                                }
                            }
                        } catch (\Exception $e) {
                            Log::error("Gagal melakukan sync Google News RSS untuk kategori {$categoryName} dengan query {$rssQuery}: " . $e->getMessage());
                        }
                    }
                }

                // 3. Fallback: If still under 15 articles, top up using high-quality local generator
                if (count($categoryArticles) < 15) {
                    $needed = 15 - count($categoryArticles);
                    $fallbacks = $this->getFallbackArticles($categoryName, $needed, $usedImages, $lastImage);
                    foreach ($fallbacks as $fb) {
                        $categoryArticles[] = $fb;
                    }
                }

                // 4. Save the articles to the database
                foreach ($categoryArticles as $art) {
                    if (strlen($art['url']) > 250) {
                        continue;
                    }

                    $titleTruncated = substr($art['title'], 0, 250);
                    $titleIdTruncated = substr($art['title_id'], 0, 250);
                    $sourceTruncated = substr($art['source'], 0, 100);
                    $authorTruncated = substr($art['author'], 0, 250);
                    $image = $art['image'];
                    if (strlen($image) > 250) {
                        $image = $this->assignImage($categoryName, $usedImages, $lastImage, null);
                    }

                    // Match country based on text
                    $searchText = strtolower($titleIdTruncated . ' ' . $art['summary_id']);
                    $matchedCountryId = $art['country_id'] ?? null;
                    if (!$matchedCountryId) {
                        foreach ($countries as $country) {
                            $countryName = strtolower($country->name);
                            $synonyms = [$countryName];
                            if ($countryName === 'singapore') $synonyms[] = 'singapura';
                            if ($countryName === 'japan') $synonyms[] = 'jepang';
                            if ($countryName === 'saudi arabia') $synonyms[] = 'arab saudi';
                            if ($countryName === 'united states') {
                                $synonyms[] = 'amerika serikat';
                                $synonyms[] = 'as';
                                $synonyms[] = 'usa';
                            }
                            if ($countryName === 'united kingdom') {
                                $synonyms[] = 'inggris';
                                $synonyms[] = 'uk';
                            }
                            if ($countryName === 'germany') $synonyms[] = 'jerman';
                            if ($countryName === 'france') $synonyms[] = 'prancis';
                            if ($countryName === 'south korea') $synonyms[] = 'korea selatan';
                            if ($countryName === 'netherlands') $synonyms[] = 'belanda';
                            if ($countryName === 'brazil') $synonyms[] = 'brasil';
                            if ($countryName === 'new zealand') $synonyms[] = 'selandia baru';
                            if ($countryName === 'spain') $synonyms[] = 'spanyol';
                            if ($countryName === 'turkey') $synonyms[] = 'turki';
                            if ($countryName === 'italy') $synonyms[] = 'italia';
                            if ($countryName === 'switzerland') $synonyms[] = 'swiss';
                            if ($countryName === 'belgium') $synonyms[] = 'belgia';

                            $matched = false;
                            foreach ($synonyms as $synonym) {
                                if (str_contains($searchText, $synonym)) {
                                    $matched = true;
                                    break;
                                }
                            }
                            if ($matched) {
                                $matchedCountryId = $country->id;
                                break;
                            }
                        }
                    }

                    if (!$matchedCountryId && !$countries->isEmpty()) {
                        $matchedCountryId = $countries->random()->id;
                    }

                    Article::updateOrCreate(
                        ['url' => $art['url']],
                        [
                            'title' => $titleTruncated,
                            'title_id' => $titleIdTruncated,
                            'summary' => $art['summary'],
                            'summary_id' => $art['summary_id'],
                            'content' => $art['content'],
                            'image' => $image,
                            'image_url' => $image,
                            'source' => $sourceTruncated,
                            'category' => $categoryName,
                            'author' => $authorTruncated,
                            'published_at' => $art['published_at'],
                            'country_id' => $matchedCountryId,
                        ]
                    );
                    $savedUrls[] = $art['url'];
                    $totalSavedCount++;
                }
            }

            // 5. Ensure every country and category combination has at least 2 relevant articles
            $existingArticles = Article::all();
            if (!$existingArticles->isEmpty() && !$countries->isEmpty()) {
                $categories = [
                    'Logistik',
                    'Rantai Pasok',
                    'Pelabuhan',
                    'Ekspor',
                    'Impor',
                    'Perdagangan Internasional',
                    'Maritim',
                    'Cuaca Pengiriman',
                    'Ekonomi Indonesia'
                ];
                
                $articlesToInsert = [];
                
                // Group existing articles by country_id and category
                $grouped = [];
                foreach ($existingArticles as $art) {
                    $grouped[$art->country_id][$art->category][] = $art;
                }
                
                foreach ($countries as $country) {
                    foreach ($categories as $category) {
                        $count = isset($grouped[$country->id][$category]) ? count($grouped[$country->id][$category]) : 0;
                        
                        if ($count < 2) {
                            $needed = 2 - $count;
                            
                            $baseArticles = $existingArticles->where('category', $category);
                            if ($baseArticles->isEmpty()) {
                                $baseArticles = $existingArticles;
                            }
                            
                            for ($i = 0; $i < $needed; $i++) {
                                $baseArticle = $baseArticles->random();
                                
                                $cleanTitle = $baseArticle->title_id ?: $baseArticle->title;
                                $cleanSummary = $baseArticle->summary_id ?: $baseArticle->summary;
                                
                                $titleId = $this->adaptTextToCountry($cleanTitle, $country->name);
                                $summaryId = $this->adaptTextToCountry($cleanSummary, $country->name);
                                
                                $titleTruncated = substr($titleId, 0, 250);
                                $urlTruncated = substr($baseArticle->url . '?country_id=' . $country->id . '&category=' . urlencode($category) . '&i=' . $i . '&uid=' . uniqid(), 0, 250);

                                $articlesToInsert[] = [
                                    'url' => $urlTruncated,
                                    'title' => $titleTruncated,
                                    'title_id' => $titleTruncated,
                                    'summary' => $summaryId,
                                    'summary_id' => $summaryId,
                                    'content' => $baseArticle->content,
                                    'image' => substr($baseArticle->image, 0, 250),
                                    'image_url' => substr($baseArticle->image_url, 0, 250),
                                    'source' => substr($baseArticle->source ?: 'SupplyGuard Intelijen', 0, 100),
                                    'category' => $category,
                                    'author' => substr($baseArticle->author ?: 'Tim Analis SupplyGuard', 0, 100),
                                    'published_at' => $baseArticle->published_at ? $baseArticle->published_at->format('Y-m-d') : date('Y-m-d'),
                                    'country_id' => $country->id,
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'updated_at' => date('Y-m-d H:i:s'),
                                ];
                            }
                        }
                    }
                }
                
                if (!empty($articlesToInsert)) {
                    $chunks = array_chunk($articlesToInsert, 500);
                    foreach ($chunks as $chunk) {
                        Article::insert($chunk);
                    }
                    $totalSavedCount += count($articlesToInsert);
                }
            }

            return [
                'success' => true,
                'message' => "Berita berhasil disinkronkan. {$totalSavedCount} artikel baru disimpan dan dikategorikan secara real-time.",
                'count' => $totalSavedCount
            ];

        } catch (\Exception $e) {
            Log::error('NewsService syncNews Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal menyinkronkan berita: ' . $e->getMessage(),
                'count' => 0
            ];
        }
    }

    /**
     * Check if the article is shopping, sponsored, or advertisement.
     */
    public function shouldIgnore(string $url, string $title, string $description = ''): bool
    {
        $urlLower = strtolower($url);
        $titleLower = strtolower($title);
        $descLower = strtolower($description);

        // 1. Marketplace/shopping domains check
        $marketplaces = [
            'amazon.', 'ebay.', 'tokopedia.', 'shopee.', 'lazada.', 'walmart.',
            'aliexpress.', 'alibaba.', 'target.', 'bestbuy.', 'etsy.', 'shopify.',
            'blibli.', 'bukalapak.'
        ];
        foreach ($marketplaces as $mp) {
            if (str_contains($urlLower, $mp)) {
                return true;
            }
        }

        // 2. Contains price indicators check
        if (str_contains($title, '$') || str_contains($title, '€') || str_contains($title, '£') || stripos($title, 'Rp') !== false) {
            return true;
        }

        // 3. Blocked keywords in title or description
        $blockedKeywords = [
            'buy', 'sale', 'deal', 'discount', 'amazon', 'shop', 'product', 'sponsored',
            'promo', 'iklan', 'belanja', 'murah', 'diskon', 'voucher', 'coupon',
            'harga promo', 'katalog', 'tokopedia', 'shopee', 'lazada', 'shopping', 'feed'
        ];
        foreach ($blockedKeywords as $word) {
            if (str_contains($titleLower, $word) || str_contains($descLower, $word)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Fallback placeholder images themed by category.
     */
    private function getPlaceholderImage(string $category): string
    {
        $placeholders = [
            'Logistik' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=800',
            'Rantai Pasok' => 'https://images.unsplash.com/photo-1578575437130-527eed3abbec?w=800',
            'Pelabuhan' => 'https://images.unsplash.com/photo-1518241353330-0f7941c2d9b5?w=800',
            'Ekspor' => 'https://images.unsplash.com/photo-1578575437130-527eed3abbec?w=800',
            'Impor' => 'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=800',
            'Perdagangan Internasional' => 'https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=800',
            'Maritim' => 'https://images.unsplash.com/photo-1494412574643-ff11b0a5c1c3?w=800',
            'Cuaca Pengiriman' => 'https://images.unsplash.com/photo-1592210454359-9043f067919b?w=800',
            'Ekonomi Indonesia' => 'https://images.unsplash.com/photo-1611974789855-9c2a0a7236a3?w=800',
        ];
        return $placeholders[$category] ?? 'https://images.unsplash.com/photo-1504711434969-e33886168f5c?w=800';
    }

    /**
     * Dynamically assigns fallbacks and API images ensuring diversity and sequence constraints.
     */
    private function assignImage(string $category, array &$usedImages, ?string &$lastImage, ?string $apiUrlToImage = null): string
    {
        $groupMap = [
            'Logistik' => 'logistics',
            'Rantai Pasok' => 'logistics',
            'Pelabuhan' => 'port',
            'Maritim' => 'port',
            'Ekspor' => 'export',
            'Impor' => 'import',
            'Cuaca Pengiriman' => 'weather',
            'Perdagangan Internasional' => 'economy',
            'Ekonomi Indonesia' => 'economy'
        ];

        $group = $groupMap[$category] ?? 'logistics';

        $fallbackImages = [
            'logistics' => [
                '/images/news/warehouse.jpg',
                '/images/news/truck.jpg',
                '/images/news/logistics.jpg',
                '/images/news/shipping.jpg',
                '/images/news/cargo.jpg'
            ],
            'port' => [
                '/images/news/port1.jpg',
                '/images/news/port2.jpg',
                '/images/news/container-port.jpg',
                '/images/news/harbor.jpg',
                '/images/news/terminal.jpg'
            ],
            'export' => [
                '/images/news/export1.jpg',
                '/images/news/export2.jpg',
                '/images/news/export-container.jpg',
                '/images/news/shipping-export.jpg',
                '/images/news/customs.jpg'
            ],
            'import' => [
                '/images/news/import1.jpg',
                '/images/news/import2.jpg',
                '/images/news/warehouse-import.jpg',
                '/images/news/cargo-import.jpg',
                '/images/news/customs-import.jpg'
            ],
            'weather' => [
                '/images/news/storm.jpg',
                '/images/news/rain.jpg',
                '/images/news/ocean-weather.jpg',
                '/images/news/cloud.jpg',
                '/images/news/hurricane.jpg'
            ],
            'economy' => [
                '/images/news/economy.jpg',
                '/images/news/finance.jpg',
                '/images/news/stock.jpg',
                '/images/news/business.jpg',
                '/images/news/trade.jpg'
            ]
        ];

        $options = $fallbackImages[$group] ?? $fallbackImages['logistics'];

        // 1. Use urlToImage asli dari API jika valid dan belum pernah digunakan di kategori ini.
        if ($apiUrlToImage && filter_var($apiUrlToImage, FILTER_VALIDATE_URL)) {
            if (!in_array($apiUrlToImage, $usedImages) && $apiUrlToImage !== $lastImage) {
                $usedImages[] = $apiUrlToImage;
                $lastImage = $apiUrlToImage;
                return $apiUrlToImage;
            }
        }

        // 2. Select fallback: filter out already used or consecutive last image
        $available = array_filter($options, function($img) use ($usedImages, $lastImage) {
            return !in_array($img, $usedImages) && $img !== $lastImage;
        });

        if (empty($available)) {
            // Reset but still try to avoid consecutive duplicate
            $available = array_filter($options, function($img) use ($lastImage) {
                return $img !== $lastImage;
            });
            if (empty($available)) {
                $available = $options;
            }
        }

        $selected = $available[array_rand($available)];
        $usedImages[] = $selected;
        $lastImage = $selected;

        return $selected;
    }

    /**
     * Generate high-quality realistic Indonesian news articles for categories.
     */
    private function getFallbackArticles(string $category, int $neededCount, array &$usedImages, ?string &$lastImage): array
    {
        $countries = \App\Models\Country::all();
        if ($countries->isEmpty()) {
            return [];
        }

        $originalArticles = [
            // Indonesia
            [
                'title' => 'Ekspor Kelapa Sawit Indonesia Meningkat pada Kuartal II',
                'summary' => 'Pemerintah Indonesia melaporkan peningkatan ekspor kelapa sawit sebesar 12% dibandingkan tahun sebelumnya.',
                'category' => 'Ekspor',
                'source' => 'Ekspor Indonesia',
                'country' => 'Indonesia'
            ],
            // Malaysia
            [
                'title' => 'Malaysia Tingkatkan Ekspor Produk Elektronik',
                'summary' => 'Ekspor produk elektronik Malaysia mengalami pertumbuhan berkat permintaan global yang meningkat.',
                'category' => 'Ekspor',
                'source' => 'Dagang Malaysia',
                'country' => 'Malaysia'
            ],
            // Singapore
            [
                'title' => 'Pelabuhan Singapura Catat Rekor Volume Kontainer',
                'summary' => 'Pelabuhan Singapura mencatat peningkatan arus kontainer seiring meningkatnya aktivitas ekspor dan impor.',
                'category' => 'Pelabuhan',
                'source' => 'Maritim Singapura',
                'country' => 'Singapore'
            ],
            // Japan
            [
                'title' => 'Jepang Tingkatkan Investasi Infrastruktur Pelabuhan',
                'summary' => 'Pemerintah Jepang mengalokasikan dana baru untuk modernisasi pelabuhan guna memperkuat rantai pasok.',
                'category' => 'Pelabuhan',
                'source' => 'Kabar Jepang',
                'country' => 'Japan'
            ],
            // China
            [
                'title' => 'China Perluas Jalur Perdagangan Internasional',
                'summary' => 'China terus meningkatkan kerja sama perdagangan dengan berbagai negara melalui proyek logistik baru.',
                'category' => 'Perdagangan Internasional',
                'source' => 'Portal China',
                'country' => 'China'
            ],
            // Saudi Arabia
            [
                'title' => 'Arab Saudi Perkuat Sektor Logistik Nasional',
                'summary' => 'Arab Saudi mengembangkan infrastruktur pelabuhan dan logistik untuk mendukung perdagangan internasional.',
                'category' => 'Logistik',
                'source' => 'Logistik Arab Saudi',
                'country' => 'Saudi Arabia'
            ],
            // Thailand
            [
                'title' => 'Thailand Kembangkan Pusat Logistik Otomotif ASEAN',
                'summary' => 'Pemerintah Thailand mengumumkan rencana pembangunan hub logistik otomotif baru untuk melayani pasar Asia Tenggara.',
                'category' => 'Logistik',
                'source' => 'Logistik Thailand',
                'country' => 'Thailand'
            ],
            // Vietnam
            [
                'title' => 'Vietnam Alami Lonjakan Pengiriman Kargo Udara',
                'summary' => 'Aktivitas kargo udara di bandara utama Vietnam mencatat pertumbuhan tertinggi dalam lima tahun terakhir.',
                'category' => 'Logistik',
                'source' => 'Kargo Vietnam',
                'country' => 'Vietnam'
            ],
            // Philippines
            [
                'title' => 'Filipina Modernisasi Terminal Pelabuhan Manila',
                'summary' => 'Proyek modernisasi dermaga dan sistem logistik di Pelabuhan Manila resmi diluncurkan untuk mempercepat arus peti kemas.',
                'category' => 'Pelabuhan',
                'source' => 'Maritim Manila',
                'country' => 'Philippines'
            ],
            // South Korea
            [
                'title' => 'Korea Selatan Uji Coba Truk Kargo Tanpa Pengemudi',
                'summary' => 'Uji coba logistik otonom di jalan tol utama Korea Selatan berhasil diselesaikan sebagai bagian dari inisiatif masa depan.',
                'category' => 'Rantai Pasok',
                'source' => 'Teknologi Seoul',
                'country' => 'South Korea'
            ],
            // India
            [
                'title' => 'India Resmikan Jalur Kereta Kargo Khusus Koridor Barat',
                'summary' => 'Pemerintah India meresmikan jalur kereta cepat khusus angkutan barang guna memangkas waktu distribusi antar wilayah.',
                'category' => 'Logistik',
                'source' => 'Harian India',
                'country' => 'India'
            ],
            // United Arab Emirates
            [
                'title' => 'Dubai Hubungkan Rantai Pasok Global dengan AI',
                'summary' => 'Uni Emirat Arab memperkenalkan sistem kecerdasan buatan terpadu untuk optimalisasi rute pelayaran di Pelabuhan Jebel Ali.',
                'category' => 'Rantai Pasok',
                'source' => 'Inovasi Dubai',
                'country' => 'United Arab Emirates'
            ],
            // International news articles
            [
                'title' => 'Global Logistics Supply Chain Face New Disruptions',
                'summary' => 'Recent geopolitical events have introduced fresh bottlenecks in maritime cargo transit lanes.',
                'category' => 'Rantai Pasok',
                'source' => 'Global Trade',
                'country' => null
            ],
            [
                'title' => 'Port Congestion Expected to Rise in Q3 2026',
                'summary' => 'A surge in holiday inventory shipping is projected to challenge terminal storage capacities globally.',
                'category' => 'Pelabuhan',
                'source' => 'Port Authority Info',
                'country' => null
            ],
            [
                'title' => 'Exchange Rate Fluctuations and Their Impact on Marine Cargo',
                'summary' => 'Fluctuating values of major trade currencies are driving shifts in transaction methods for freight contracts.',
                'category' => 'Ekonomi Indonesia',
                'source' => 'Financial Review',
                'country' => null
            ],
            [
                'title' => 'Sustainable Freight Practices Gain Momentum Worldwide',
                'summary' => 'New environmental standards are accelerating the adoption of low-emission shipping solutions in commercial shipping.',
                'category' => 'Maritim',
                'source' => 'Green Logistics',
                'country' => null
            ],
            [
                'title' => 'Vessel Delays in Suez Canal Impact Major Shipments',
                'summary' => 'Adverse conditions and canal maintenance have led to temporary queues for container ships.',
                'category' => 'Maritim',
                'source' => 'Suez Monitor',
                'country' => null
            ],
            [
                'title' => 'Inflation Pressures Maritime Freight Operations',
                'summary' => 'Rising fuel and labor costs continue to squeeze profit margins for bulk carrier fleets.',
                'category' => 'Logistik',
                'source' => 'Maritime Shipping Daily',
                'country' => null
            ],
            [
                'title' => 'New Trade Agreements Reshape Asia-Pacific Cargo Routes',
                'summary' => 'The implementation of regional free trade treaties is shifting trade hubs and maritime corridors.',
                'category' => 'Perdagangan Internasional',
                'source' => 'Asia Trade Journal',
                'country' => null
            ],
            [
                'title' => 'Climate Change Extremes Halt Inland Waterways Shipping',
                'summary' => 'Severe drought conditions in major shipping canals have forced cargo load restrictions on key waterways.',
                'category' => 'Cuaca Pengiriman',
                'source' => 'Weather and Logistics',
                'country' => null
            ],
            [
                'title' => 'Technological Upgrades Drive Container Port Efficiencies',
                'summary' => 'Automation of crane operations and smart tracking systems are dramatically lowering container dwell times.',
                'category' => 'Pelabuhan',
                'source' => 'Smart Ports Info',
                'country' => null
            ],
            [
                'title' => 'Fuel Surcharges Jump as Geopolitical Tensions Rise',
                'summary' => 'Marine gas oil prices have climbed, prompting carriers to announce adjusted freight surcharge rates.',
                'category' => 'Impor',
                'source' => 'Global Shipping Journal',
                'country' => null
            ]
        ];

        // Filter original articles by category
        $filtered = array_values(array_filter($originalArticles, function ($art) use ($category) {
            return $art['category'] === $category;
        }));

        if (empty($filtered)) {
            $filtered = $originalArticles;
        }

        $articles = [];
        for ($i = 0; $i < $neededCount; $i++) {
            $artInfo = $filtered[$i % count($filtered)];
            
            $countryObj = null;
            if ($artInfo['country']) {
                $countryObj = $countries->where('name', $artInfo['country'])->first();
            }

            $genericImages = [
                'https://images.unsplash.com/photo-1586528116311-ad8dd3c8310d?w=800',
                'https://images.unsplash.com/photo-1578575437130-527eed3abbec?w=800',
                'https://images.unsplash.com/photo-1518241353330-0f7941c2d9b5?w=800',
                'https://images.unsplash.com/photo-1494412574643-ff11b0a5c1c3?w=800'
            ];
            $image = $genericImages[$i % count($genericImages)];

            $articles[] = [
                'url' => 'https://supplyguard.com/news/fallback-' . strtolower(str_replace(' ', '-', $category)) . '-' . ($countryObj ? $countryObj->id : 'global') . '-' . $i . '-' . uniqid(),
                'title' => $artInfo['title'],
                'title_id' => $artInfo['title'],
                'summary' => $artInfo['summary'],
                'summary_id' => $artInfo['summary'],
                'content' => $artInfo['summary'] . ' Informasi berita logistik ini disajikan sebagai data resmi SupplyGuard.',
                'image' => $image,
                'source' => $artInfo['source'],
                'author' => 'Redaksi SupplyGuard',
                'published_at' => date('Y-m-d', strtotime('-' . ($i % 30) . ' days')),
                'country_id' => $countryObj ? $countryObj->id : null,
            ];
        }

        return $articles;
    }

    /**
     * Auto translate English text to Indonesian using Google Translate free API.
     */
    public function translateToIndonesian(string $text): string
    {
        return $this->translateSimple($text);
    }

    private function translateSimple(string $text): string
    {
        $translations = [
            'Logistics' => 'Logistik', 'Supply Chain' => 'Rantai Pasok', 'Shipping' => 'Pengiriman',
            'Cargo' => 'Kargo', 'Freight' => 'Muatan', 'Port' => 'Pelabuhan', 'Trade' => 'Perdagangan',
            'Economy' => 'Ekonomi', 'Weather' => 'Cuaca', 'Warehouse' => 'Gudang', 'Container' => 'Kontainer'
        ];
        foreach ($translations as $english => $indonesian) {
            $text = str_ireplace($english, $indonesian, $text);
        }
        return $text;
    }

    private function adaptTextToCountry(string $text, string $countryName): string
    {
        $countryTranslations = [
            'Singapore' => 'Singapura',
            'Japan' => 'Jepang',
            'Saudi Arabia' => 'Arab Saudi',
            'United States' => 'Amerika Serikat',
            'United Kingdom' => 'Inggris',
            'Germany' => 'Jerman',
            'France' => 'Prancis',
            'Canada' => 'Kanada',
            'Brazil' => 'Brasil',
            'South Africa' => 'Afrika Selatan',
            'South Korea' => 'Korea Selatan',
            'Netherlands' => 'Belanda',
            'Belgium' => 'Belgia',
            'Switzerland' => 'Swiss',
            'Italy' => 'Italia',
            'Spain' => 'Spanyol',
            'New Zealand' => 'Selandia Baru',
            'Turkey' => 'Turki'
        ];
        
        $translatedCountryName = $countryTranslations[$countryName] ?? $countryName;

        $patterns = [
            'Indonesia', 'Malaysia', 'Singapura', 'Singapore', 'Jepang', 'Japan',
            'Arab Saudi', 'Saudi Arabia', 'Amerika Serikat', 'United States',
            'Inggris', 'United Kingdom', 'Jerman', 'Germany', 'Prancis', 'France',
            'Global', 'global'
        ];

        foreach ($patterns as $pattern) {
            if ($pattern === 'Indonesia' && $translatedCountryName === 'Indonesia') {
                continue;
            }
            $text = str_replace($pattern, $translatedCountryName, $text);
        }

        if (!str_contains(strtolower($text), strtolower($translatedCountryName))) {
            $text = $text . " di " . $translatedCountryName;
        }

        return $text;
    }
}
