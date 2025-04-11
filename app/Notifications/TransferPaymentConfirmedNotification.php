<?php

namespace App\Notifications;

use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TransferPaymentConfirmedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $payment;

    /**
     * Create a new notification instance.
     */
    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Transfer Payment Confirmed')
            ->greeting('Hello ' . $notifiable->name)
            ->line('Your transfer payment has been confirmed.')
            ->line('Payment Details:')
            ->line('Amount: ' . number_format($this->payment->amount, 2))
            ->line('Method: ' . ucfirst(str_replace('_', ' ', $this->payment->payment_method)))
            ->line('Reference: ' . $this->payment->reference)
            ->line('Transaction Code: ' . $this->payment->transaction_code)
            ->line('Receipt Number: ' . $this->payment->receipt_number)
            ->action('View Payment', url('/payments/' . $this->payment->id))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'payment_id' => $this->payment->id,
            'amount' => $this->payment->amount,
            'payment_method' => $this->payment->payment_method,
            'reference' => $this->payment->reference,
            'transaction_code' => $this->payment->transaction_code,
            'receipt_number' => $this->payment->receipt_number,
            'message' => 'Transfer payment confirmed',
            'timestamp' => now()->toDateTimeString()
        ];
    }
} 