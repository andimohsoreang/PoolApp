<?php

namespace App\Events;

use App\Models\Notification;
use App\Models\Reservation;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NewReservationNotification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $notification;
    public $reservation;

    /**
     * Create a new event instance.
     */
    public function __construct(Notification $notification, Reservation $reservation)
    {
        $this->notification = $notification;
        $this->reservation = $reservation;

        // Send notification to Socket.IO server directly
        $this->sendToSocketServer();
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('admin-notifications'),
        ];
    }

    /**
     * The event's broadcast name.
     */
    public function broadcastAs(): string
    {
        return 'new-reservation';
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        return [
            'id' => $this->notification->id,
            'type' => $this->notification->type,
            'message' => $this->notification->message,
            'created_at' => $this->notification->created_at->diffForHumans(),
            'reservation' => [
                'id' => $this->reservation->id,
                'code' => $this->reservation->reservation_code,
                'status' => $this->reservation->status,
                'customer_name' => $this->reservation->customer->name
            ]
        ];
    }

    /**
     * Send notification to Socket.IO server
     *
     * This method sends the notification data directly to our Socket.IO server
     * to ensure real-time delivery even if Laravel broadcasting doesn't work.
     */
    private function sendToSocketServer(): void
    {
        try {
            $data = $this->broadcastWith();

            $socketUrl = env('SOCKET_SERVER_URL', 'http://localhost:6001');
            $endpoint = $socketUrl . '/broadcast';

            // Debug socket URL
            Log::info('Socket server configuration', [
                'socket_url' => $socketUrl,
                'endpoint' => $endpoint,
                'socket_env_var_exists' => env('SOCKET_SERVER_URL') ? 'yes' : 'no'
            ]);

            Log::info('Sending notification to Socket.IO server', [
                'notification_id' => $this->notification->id,
                'notification_data' => $data,
                'channel' => 'admin-notifications',
                'event' => 'new-reservation',
                'endpoint' => $endpoint
            ]);

            // Send to Socket.IO server with retry logic
            $maxRetries = 3;
            $retryCount = 0;
            $success = false;

            while (!$success && $retryCount < $maxRetries) {
                try {
                    $response = Http::timeout(5)->post($endpoint, [
                        'channel' => 'admin-notifications',
                        'event' => 'new-reservation',
                        'data' => $data
                    ]);

                    if ($response->successful()) {
                        Log::info('Successfully sent notification to Socket.IO server', [
                            'response' => $response->json(),
                            'retry' => $retryCount
                        ]);
                        $success = true;
                    } else {
                        Log::warning('Failed to send notification to Socket.IO server - Attempt ' . ($retryCount + 1), [
                            'status' => $response->status(),
                            'response' => $response->body()
                        ]);
                        $retryCount++;

                        if ($retryCount < $maxRetries) {
                            // Wait before retrying (exponential backoff)
                            sleep(1 * $retryCount);
                        }
                    }
                } catch (\Illuminate\Http\Client\ConnectionException $e) {
                    Log::warning('Connection error when sending to socket server - Attempt ' . ($retryCount + 1), [
                        'exception' => get_class($e),
                        'message' => $e->getMessage()
                    ]);

                    $retryCount++;
                    if ($retryCount < $maxRetries) {
                        sleep(1 * $retryCount);
                    }
                }
            }

            if (!$success) {
                Log::error('All attempts to send notification to socket server failed');
            }
        } catch (\Exception $e) {
            // Log the error but don't throw it to prevent affecting the application flow
            Log::error('Exception when sending notification to socket server: ' . $e->getMessage(), [
                'exception' => get_class($e),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
