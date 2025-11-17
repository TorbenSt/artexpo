<?php

namespace App\Jobs;

use App\Models\Image;
use App\Models\SocialMediaPost;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use OpenAI;

class GenerateSocialMediaPostsJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    public function __construct(protected Image $image) {}

    public function handle()
    {
        if (!$this->image->for_social_media || !$this->image->visible) {
            return;
        }

        $exhibition = $this->image->exhibition;
        $networks = config('socialmedia.networks', []);

        foreach ($networks as $network) {
            $prompt = $this->buildPrompt($network, $exhibition, $this->image);

            try {
                $response = OpenAI::client(config('openai.api_key'))->chat()->create([
                    'model' => config('openai.model', 'gpt-4o'),
                    'messages' => [
                        ['role' => 'system', 'content' => 'Du bist ein Social-Media-Manager für ein Museum. Schreibe einen ansprechenden Post auf Deutsch.'],
                        ['role' => 'user', 'content' => $prompt],
                    ],
                    'max_tokens' => 300,
                ]);

                $content = $response->choices[0]->message->content;

                SocialMediaPost::create([
                    'image_id' => $this->image->id,
                    'network' => $network,
                    'content' => $content,
                    'status' => 'draft',
                ]);
            } catch (\Exception $e) {
                SocialMediaPost::create([
                    'image_id' => $this->image->id,
                    'network' => $network,
                    'content' => 'Fehler: ' . $e->getMessage(),
                    'status' => 'failed',
                ]);
            }
        }
    }

    private function buildPrompt($network, $exhibition, $image): string
    {
        $networkNames = [
            'instagram' => 'Instagram',
            'facebook' => 'Facebook',
            'twitter' => 'X (Twitter)',
            'linkedin' => 'LinkedIn',
        ];

        $name = $networkNames[$network] ?? $network;

        return "Erstelle einen Social-Media-Post für **{$name}** über die Ausstellung:

        Titel: {$exhibition->title}
        Künstler: {$exhibition->artist}
        Laufzeit: {$exhibition->start_date->format('d.m.Y')} – {$exhibition->end_date->format('d.m.Y')}
        Beschreibung: {$exhibition->intro_text}

        Verwendetes Bild:
        - Credits: {$image->credits}
        - Position: {$image->position}

        Schreibe einen **ansprechenden, kurzen Post** (max. 280 Zeichen für X, sonst bis 600).  
        Verwende Emojis, Call-to-Action (z. B. 'Jetzt Tickets sichern!') und Hashtags (#Kunst #Museum #{$exhibition->title}).  
        Füge Bildunterschrift ein, falls nötig.";
    }
}