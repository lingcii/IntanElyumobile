<?php

namespace App\Mail;

use App\Models\Itinerary;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PlannedTripMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public Itinerary $itinerary
    ) {}

    public function envelope(): Envelope
    {
        $title = $this->itinerary->title ?: 'My La Union Trip';
        return new Envelope(
            subject: "🗓️ Planned Trip Confirmed: {$title} - Feel La Union",
        );
    }

    public function content(): Content
    {
        $stops = $this->itinerary->items
            ? $this->itinerary->items->sortBy('order_index')->map(function ($item) {
                return $item->destination?->name ?? 'Destination';
            })->filter()->values()->all()
            : [];

        return new Content(
            view: 'emails.planned_trip',
            with: [
                'userName'  => $this->user->name ?: 'Explorer',
                'tripTitle' => $this->itinerary->title ?: 'My La Union Trip',
                'tripDate'  => $this->itinerary->trip_date ? $this->itinerary->trip_date->format('F j, Y') : 'Upcoming',
                'totalPax'  => (int) ($this->itinerary->total_pax ?: 1),
                'totalCost' => number_format((float) ($this->itinerary->total_cost ?? 0), 2),
                'stops'     => $stops,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
