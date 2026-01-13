<?php

namespace App\Jobs;

use App\AI\Rag\FunFactsRetriever;
use App\Models\Image;
use App\Models\SocialMediaPost;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Log;
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
                $content = $this->enforceNetworkRules($network, $content);

                SocialMediaPost::create([
                    'image_id' => $this->image->id,
                    'network' => $network,
                    'content' => $content,
                    'status' => 'draft',
                ]);

                Log::info("Generated social media post", [
                    'image_id' => $this->image->id,
                    'network' => $network,
                    'exhibition' => $exhibition->title,
                ]);
            } catch (\Exception $e) {
                Log::error("Failed to generate social media post", [
                    'image_id' => $this->image->id,
                    'network' => $network,
                    'error' => $e->getMessage(),
                ]);

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

        // Build fun facts query
        $query = $this->buildFunFactsQuery($exhibition, $image);
        $funFacts = app(FunFactsRetriever::class)
            ->withContext($image->id, $network)
            ->retrieveWithFallback($query, 6);

        // Format fun facts section
        $funFactsBlock = '';
        if (!empty($funFacts)) {
            $funFactsBlock = "\n\nFUN FACTS (verifizierte Quellen):\n";
            foreach ($funFacts as $index => $fact) {
                $funFactsBlock .= ($index + 1) . ". {$fact['text']}\n";
                $funFactsBlock .= "   Quelle: {$fact['source']} - {$fact['title']}\n";
                if ($fact['url']) {
                    $funFactsBlock .= "   URL: {$fact['url']}\n";
                }
            }
            $funFactsBlock .= "\nVerwende diese Fun Facts, wenn sie relevant sind. Erfinde keine Fakten, die nicht in den obigen Quellen stehen!";
        } else {
            $funFactsBlock = "\n\nKeine Fun Facts verfügbar. Erstelle einen generischen Post ohne erfundene Fakten.";
        }

        return "Erstelle einen Social-Media-Post für **{$name}** über die Ausstellung:

        Titel: {$exhibition->title}
        Künstler: {$exhibition->artist}
        Laufzeit: {$exhibition->start_date->format('d.m.Y')} – {$exhibition->end_date->format('d.m.Y')}
        Beschreibung: {$exhibition->intro_text}

        Verwendetes Bild:
        - Credits: {$image->credits}
        - Position: {$image->position}

        {$funFactsBlock}

        Schreibe einen **ansprechenden, kurzen Post** (max. 280 Zeichen für X, sonst bis 600).
        Verwende Emojis, Call-to-Action (z. B. 'Jetzt Tickets sichern!') und Hashtags (#Kunst #Museum #{$exhibition->title}).
        Füge Bildunterschrift ein, falls nötig.";
    }

    /**
     * Build query for fun facts retrieval.
     */
    private function buildFunFactsQuery($exhibition, $image): string
    {
        $parts = [
            $exhibition->artist ?? '',
            $exhibition->title ?? '',
            $image->credits ?? '',
        ];

        // Filter out empty parts and join
        $parts = array_filter($parts);
        return implode(' ', $parts);
    }

    /**
     * Enforce network character limits.
     */
    private function enforceNetworkRules(string $network, string $content): string
    {
        $limits = config('socialmedia.limits', []);
        $limit = $limits[$network] ?? null;

        if ($limit && strlen($content) > $limit) {
            return substr($content, 0, $limit - 3) . '...';
        }

        return $content;
    }
}