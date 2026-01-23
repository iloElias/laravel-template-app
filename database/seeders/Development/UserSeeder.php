<?php

namespace Database\Seeders\Development;

use App\Models\Hr\User;

class UserSeeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'uuid' => '4ad8ae95-3159-4cf7-a5e6-7a8711be75e2',
            'name' => 'Murilo Elias',
            'surname' => 'Santos Figueiredo',
            'email' => 'murilo7456@gmail.com',
            'number' => null,
            'password' => '$2y$12$3OjU9yfwvT4KbgjrSe2zdegXF8kSbIREv0Kf1vyagJWyhdBWZDCWW',
            'profile_type' => 'requester',
            'language' => 'pt-BR',
            'email_verified' => true,
            'email_verified_at' => '2025-05-12 13:26:09',
            'number_verified' => false,
            'number_verified_at' => null,
            'profile_picture' => null,
            'active' => true,
            'created_at' => '2025-05-02 09:03:02',
            'updated_at' => '2025-05-12 13:26:09',
            'inactivated_at' => null,
        ]);

        User::create([
            'uuid' => 'f18b06cb-5c2b-4852-a765-3e6f22c59992',
            'name' => 'Agrofast',
            'surname' => 'Support',
            'email' => 'contact.agrofast@gmail.com',
            'number' => null,
            'password' => '$2y$12$WIeQcnq4wF443lv.l9V3duATOm8EKuCiA5csHzJ82pTKNfx0hl7bq',
            'profile_type' => 'transporter',
            'language' => 'pt-BR',
            'email_verified' => true,
            'email_verified_at' => '2025-05-12 13:38:01',
            'number_verified' => false,
            'number_verified_at' => null,
            'profile_picture' => null,
            'active' => true,
            'created_at' => '2025-05-12 13:37:55',
            'updated_at' => '2025-05-12 13:38:08',
            'inactivated_at' => null,
        ]);
    }
}
