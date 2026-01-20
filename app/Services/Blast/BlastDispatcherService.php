<?php

namespace App\Services\Blast;

use App\DataTransferObjects\BlastPayload;
use App\Jobs\Blast\SendEmailBlastJob;
use App\Jobs\Blast\SendWhatsappBlastJob;
use App\Models\BlastMessage;
use App\Models\BlastTarget;
use App\Models\BlastLog;
use Illuminate\Support\Facades\Auth;

class BlastDispatcherService
{
    public function dispatch(
        string $channel,
        array $targets,
        BlastPayload $payload
    ): BlastMessage {

        // 1. Simpan blast message
        $blastMessage = BlastMessage::create([
            'channel'    => $channel,
            'message'    => $payload->message,
            'meta'       => $payload->meta,
            'created_by' => Auth::id(),
        ]);

        // 2. Loop target
        foreach ($targets as $target) {

            BlastTarget::create([
                'blast_message_id' => $blastMessage->id,
                'target'           => $target,
            ]);

            // 3. BUAT LOG (PAKAI MODEL LAMA)
            BlastLog::create([
                'channel'        => $channel,
                'target'         => $target,
                'status'         => 'PENDING',
                'reference_type' => BlastMessage::class,
                'reference_id'   => $blastMessage->id,
            ]);

            // 4. Dispatch Job
            if ($channel === 'WHATSAPP') {
                dispatch(new SendWhatsappBlastJob($target, $payload));
            }

            if ($channel === 'EMAIL') {
                dispatch(new SendEmailBlastJob($target, $payload));
            }
        }

        return $blastMessage;
    }
}
