@include('emails.partials.header', ['title' => $notification->title])

<p style="margin:0 0 12px;">{{ $notification->message }}</p>

@include('emails.partials.footer')
