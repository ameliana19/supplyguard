<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Relasi One-to-One dengan model Profile
     */
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    /**
     * Delegasikan attribute profil ke relasi One-to-One
     */
    public function getPhoneAttribute()
    {
        return $this->profile?->phone_number;
    }

    public function getPhoneNumberAttribute()
    {
        return $this->profile?->phone_number;
    }

    public function getCompanyAttribute()
    {
        return $this->profile?->company;
    }

    public function getRoleAttribute()
    {
        return $this->attributes['role'] ?? ($this->profile?->role ?? 'user');
    }

    public function getAddressAttribute()
    {
        return $this->profile?->address;
    }

    public function getPhotoAttribute()
    {
        return $this->profile?->photo;
    }

    public function getProfilePhotoAttribute()
    {
        return $this->profile?->photo;
    }

    /**
     * Mutator untuk delegasi penyimpanan data ke profiles
     */
    public function setPhoneAttribute($value)
    {
        $this->getOrCreateProfile()->phone_number = $value;
    }

    public function setPhoneNumberAttribute($value)
    {
        $this->getOrCreateProfile()->phone_number = $value;
    }

    public function setCompanyAttribute($value)
    {
        $this->getOrCreateProfile()->company = $value;
    }

    public function setRoleAttribute($value)
    {
        $this->attributes['role'] = $value;
        $this->getOrCreateProfile()->role = $value;
    }

    public function setAddressAttribute($value)
    {
        $this->getOrCreateProfile()->address = $value;
    }

    public function setPhotoAttribute($value)
    {
        $this->getOrCreateProfile()->photo = $value;
    }

    public function setProfilePhotoAttribute($value)
    {
        $this->getOrCreateProfile()->photo = $value;
    }

    private function getOrCreateProfile()
    {
        if (!$this->relationLoaded('profile') || !$this->profile) {
            $profile = $this->profile()->firstOrCreate([
                'user_id' => $this->id
            ], [
                'full_name' => $this->name
            ]);
            $this->setRelation('profile', $profile);
        }
        return $this->profile;
    }
}
