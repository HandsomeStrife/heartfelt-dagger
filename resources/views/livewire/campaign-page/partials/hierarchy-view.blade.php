@foreach($pages as $page)
    @include('livewire.campaign-page.partials.hierarchy-page-item', ['page' => $page, 'depth' => 0])
@endforeach