<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Toplu atama yapılabilecek nitelikler.
     * Yeni eklediğimiz sosyal medya ve iletişim alanlarını buraya dahil ettik.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name', 
        'email', 
        'password', 
        'role', 
        'author_name', 
        'avatar', 
        'bio', 
        'is_columnist',
        'phone',
        'website',
        'facebook',
        'twitter',
        'linkedin',
        'youtube'
    ];

    /**
     * Serileştirme sırasında gizli tutulacak nitelikler.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Veri tipi dönüşümleri.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_columnist' => 'boolean',
        ];
    }

    /**
     * Yardımcı Metot: Kullanıcının köşe yazarı olup olmadığını kontrol eder.
     * Hem rolü kontrol eder hem de eski is_columnist bayrağına bakar.
     */
    public function isColumnist()
    {
        return $this->role === 'columnist' || $this->is_columnist;
    }

    /**
     * Yardımcı Metot: Kullanıcının yönetici olup olmadığını kontrol eder.
     */
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

public function articles()
{
    return $this->hasMany(\App\Models\Post::class)->where('content_kind', 'article');
}

public function posts()
{
    return $this->hasMany(\App\Models\Post::class)->where('content_kind', 'news');
}


}