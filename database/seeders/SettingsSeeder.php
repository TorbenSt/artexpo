<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
	/**
	 * Run the database seeds.
	 */
	public function run(): void
	{
		$now = now();

		// Einfacher Seeder: nur die auswählbaren Social-Media-Kanäle speichern
		DB::table('settings')->updateOrInsert(
			['key' => 'social_media_networks'],
			[
				'value' => json_encode(['instagram', 'facebook', 'twitter']),
				'created_at' => $now,
				'updated_at' => $now,
			]
		);
	}
}

