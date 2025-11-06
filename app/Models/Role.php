<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $table = 'roles';

    protected $fillable = [
        'name',
        'description',
    ];

    /**
     * Get users with this role
     */
    public function users()
    {
        return $this->hasMany(User::class, 'role_id', 'id');
    }

    /**
     * Role constants
     */
    const ROLE_ADMIN = 1;
    const ROLE_OWNER = 2;
    const ROLE_PETERNAK = 3;

    /**
     * Get role ID by name
     */
    public static function getIdByName($name)
    {
        return self::where('name', $name)->value('id');
    }
}
