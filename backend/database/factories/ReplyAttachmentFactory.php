<?php

namespace Database\Factories;

use App\Models\TicketReply;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReplyAttachmentFactory extends Factory
{
    public function definition(): array
    {
        $attachments = [
            'storage/examples/1.jpg',
            'storage/examples/2.jpg',
            'storage/examples/3.jpg',
            'storage/examples/4.jpg',
            'storage/examples/5.jpg',
            'storage/examples/6.jpg',
            'storage/examples/7.jpg',
            'storage/examples/8.jpg',
            'storage/examples/9.jpg',
            'storage/examples/10.jpg',
        ];

        return [
            'filename'        => fake()->slug(),
            'path'            => fake()->randomElement($attachments),
            'ticket_reply_id' => TicketReply::query()->inRandomOrder()->first()->id,
        ];
    }
}
