<?php
// Check DSP001 current status in middleware DB

$d = App\Models\DisplayDevice::where('display_id', 'DSP001')->first();
if ($d) {
    $diff = $d->last_heartbeat_at ? now()->diffInSeconds($d->last_heartbeat_at) : 'N/A';
    echo "ID: {$d->display_id}, Status: {$d->status}, Last HB: {$d->last_heartbeat_at} ({$diff}s ago)\n";
} else {
    echo "DSP001 not found\n";
}
