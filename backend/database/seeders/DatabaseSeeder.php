<?php

namespace Database\Seeders;

use App\Enums\InteractionType;
use App\Models\Comment;
use App\Models\Interaction;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Curated, demo-ready dataset: a viewer, named style creators, editorial
     * posts with real imagery, a follow graph, and layered engagement so the
     * ranked, personalised feed looks alive.
     */
    public function run(): void
    {
        // The account the mobile app signs in as (demo credentials).
        $viewer = User::factory()->create([
            'name' => 'Alice Rivera',
            'email' => 'alice@example.com',
            'authenticity_score' => 0.90,
        ]);

        $creators = collect([
            ['name' => 'Maya Chen', 'email' => 'maya@example.com', 'authenticity_score' => 0.96],
            ['name' => 'Diego Torres', 'email' => 'diego@example.com', 'authenticity_score' => 0.88],
            ['name' => 'Priya Kapoor', 'email' => 'priya@example.com', 'authenticity_score' => 0.93],
            ['name' => 'Jonas Lind', 'email' => 'jonas@example.com', 'authenticity_score' => 0.79],
            ['name' => 'Amara Okoye', 'email' => 'amara@example.com', 'authenticity_score' => 0.91],
        ])->map(fn (array $attrs) => User::factory()->create($attrs));

        // A wider audience so posts have realistic engagement counts.
        $audience = User::factory(14)->create();
        $everyone = $creators->concat($audience)->push($viewer);

        $posts = collect($this->posts())->map(function (array $item, int $i) use ($creators): Post {
            $author = $creators[$i % $creators->count()];

            return Post::factory()->for($author)->create([
                'caption' => $item['caption'],
                'image_url' => $item['image'],
                'created_at' => now()->subHours($i * 5 + 1),
            ]);
        });

        // Follow graph: the viewer follows two creators; creators follow each other.
        $viewer->following()->attach([$creators[0]->id, $creators[2]->id]);
        $creators->each(fn (User $c) => $c->following()->attach(
            $creators->where('id', '!=', $c->id)->random(2)->pluck('id')->all()
        ));

        // Layered engagement: each post gets a mix of emoji reactions and views.
        $reactionTypes = [
            InteractionType::Like->value,
            InteractionType::Fire->value,
            InteractionType::Clap->value,
            InteractionType::Like->value,
            InteractionType::View->value,
        ];

        $posts->each(function (Post $post) use ($everyone, $reactionTypes): void {
            $fans = $everyone->where('id', '!=', $post->user_id)->shuffle()->take(random_int(5, 16));
            foreach ($fans as $fan) {
                Interaction::factory()->for($fan)->for($post)->create([
                    'type' => $reactionTypes[array_rand($reactionTypes)],
                ]);
            }

            // A few comments per post.
            $commenters = $everyone->where('id', '!=', $post->user_id)->shuffle()->take(random_int(1, 4));
            foreach ($commenters as $commenter) {
                Comment::factory()->for($commenter)->for($post)->create([
                    'body' => $this->commentBodies()[array_rand($this->commentBodies())],
                ]);
            }
        });

        // The viewer's own taste: likes/saves that seed the personalised feed.
        $this->viewerTaste($viewer, $posts);
    }

    /** Give the viewer a clear taste signal for the ranking demo. */
    private function viewerTaste(User $viewer, Collection $posts): void
    {
        $liked = $posts->random(min(6, $posts->count()));
        foreach ($liked as $post) {
            Interaction::factory()->for($viewer)->for($post)->create([
                'type' => InteractionType::Like->value,
            ]);
        }
    }

    /**
     * @return list<string>
     */
    private function commentBodies(): array
    {
        return [
            'Obsessed with this. 😍',
            'Where is the coat from?',
            'The proportions here are perfect.',
            'Saving this for autumn inspo.',
            'That colour on you 🔥',
            'Need the full breakdown please!',
            'Effortless as always.',
            'This is my whole aesthetic.',
            'Clean lines — love it.',
            'Ok but the shoes though 👟',
            'Styling goals, honestly.',
            'How is this so good every time?',
        ];
    }

    /**
     * @return array<int, array{caption: string, image: string}>
     */
    private function posts(): array
    {
        $img = fn (string $id): string => "https://images.unsplash.com/photo-{$id}?w=800&q=80&auto=format&fit=crop";

        return [
            ['caption' => 'Camel wool overcoat over an all-black base. The timeless winter uniform.', 'image' => $img('1490481651871-ab68de25d43d')],
            ['caption' => 'Linen co-ord in oat — slow Sunday mornings and iced coffee.', 'image' => $img('1483985988355-763728e1935b')],
            ['caption' => 'Monochrome tailoring: boxy shoulder, cropped ankle, zero fuss.', 'image' => $img('1445205170230-053b83016050')],
            ['caption' => 'Vintage denim, a white tee, gold hoops. Nothing more, nothing less.', 'image' => $img('1441984904996-e0b6ba687e04')],
            ['caption' => 'Trench season. Belted, oversized, thrown over soft knitwear.', 'image' => $img('1523381210434-271e8be1f52b')],
            ['caption' => 'Earth-tone knit and wide-leg trousers for a crisp autumn walk.', 'image' => $img('1479064555552-3ef4979f8908')],
            ['caption' => 'A sharp blazer, a slip dress, and boots that mean business.', 'image' => $img('1521572163474-6864f9cf17ab')],
            ['caption' => 'Slip skirt meets chunky loafers — soft against structured.', 'image' => $img('1434389677669-e08b4cac3105')],
            ['caption' => 'Head-to-toe cream. Let the texture do all the talking.', 'image' => $img('1487222477894-8943e31ef7b2')],
            ['caption' => 'Leather jacket over a floral midi. Contrast is the whole point.', 'image' => $img('1515886657613-9f3515b0c78f')],
            ['caption' => 'Tailored suit, no tie, clean sneakers. Boardroom to bar.', 'image' => $img('1529139574466-a303027c1d8b')],
            ['caption' => 'An oversized shirt as a dress, cinched with a thin leather belt.', 'image' => $img('1509631179647-0177331693ae')],
            ['caption' => 'Coastal neutrals: sand, ecru, and a woven straw tote.', 'image' => $img('1554568218-0f1715e72254')],
            ['caption' => 'Statement coat, quiet everything else.', 'image' => $img('1496747611176-843222e1e57c')],
            ['caption' => 'Pinstripe waistcoat and wide trousers. Menswear, borrowed and better.', 'image' => $img('1485462537746-965f33f7f6a7')],
            ['caption' => 'A knit vest over a crisp poplin shirt. Preppy, reworked.', 'image' => $img('1467043198406-dc953a3defa0')],
        ];
    }
}
