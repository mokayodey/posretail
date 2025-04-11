<?php

namespace App\Listeners;

use App\Events\PaymentReceived;
use App\Events\TransferPaymentReceived;
use App\Notifications\PaymentReceivedNotification;
use App\Notifications\TransferPaymentConfirmedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendPaymentNotification implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(PaymentReceived $event): void
    {
        // Notify branch admins
        $branchAdmins = $event->payment->cart->branch->users()
            ->whereHas('roles', function ($query) {
                $query->where('name', 'branch_admin');
            })
            ->get();

        foreach ($branchAdmins as $admin) {
            $admin->notify(new PaymentReceivedNotification($event->payment));
        }

        // Notify system admins
        $systemAdmins = \App\Models\User::role('admin')->get();
        foreach ($systemAdmins as $admin) {
            $admin->notify(new PaymentReceivedNotification($event->payment));
        }
    }

    /**
     * Handle transfer payment confirmation.
     */
    public function handleTransferPayment(TransferPaymentReceived $event): void
    {
        // Notify the user who initiated the payment
        $event->payment->user->notify(
            new TransferPaymentConfirmedNotification($event->payment)
        );

        // Notify branch admins
        $branchAdmins = $event->payment->cart->branch->users()
            ->whereHas('roles', function ($query) {
                $query->where('name', 'branch_admin');
            })
            ->get();

        foreach ($branchAdmins as $admin) {
            $admin->notify(new TransferPaymentConfirmedNotification($event->payment));
        }
    }
} 