<?php

namespace App\Events;

use App\Models\Upload;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class UploadStatusUpdated implements ShouldBroadcastNow
{
    use SerializesModels;

    public $upload;

    public function __construct(Upload $upload)
    {
        $this->upload = $upload;
    }

    public function broadcastOn(): Channel
    {
        return new Channel('uploads');
    }

    public function broadcastAs(): string
    {
        return 'status.updated';
    }
}
