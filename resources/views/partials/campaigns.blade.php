@foreach ($campaigns as $campaign)
    @include('includes.campaign-card', ['campaign' => $campaign])
@endforeach