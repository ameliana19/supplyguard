<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            // Ubah kolom date menjadi published_at untuk lebih spesifik
            $table->renameColumn('date', 'published_at');
            
            // Tambah kolom author jika belum ada
            if (!Schema::hasColumn('articles', 'author')) {
                $table->string('author')->nullable()->after('category');
            }
            
            // Tambah kolom baru untuk fitur berita lengkap
            $table->string('title_id')->nullable()->after('title'); // Judul terjemahan Bahasa Indonesia
            $table->text('summary')->nullable()->after('title_id'); // Ringkasan berita
            $table->text('summary_id')->nullable()->after('summary'); // Ringkasan terjemahan Bahasa Indonesia
            $table->longText('content')->nullable()->after('summary_id'); // Isi berita lengkap
            $table->string('image')->nullable()->after('content'); // URL gambar
            $table->string('source')->nullable()->after('image'); // Sumber berita (media name)
            $table->string('url')->nullable()->after('source'); // URL berita asli
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            // Hapus kolom yang ditambahkan
            $table->dropColumn(['title_id', 'summary', 'summary_id', 'content', 'image', 'source', 'url']);
            
            // Rename kembali published_at ke date
            $table->renameColumn('published_at', 'date');
        });
    }
};
