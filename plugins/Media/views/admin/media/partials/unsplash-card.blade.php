<div class="media-unsplash-card">
    <img src="{{ $photo['thumb'] }}" alt="{{ $photo['description'] }}">
    <div class="media-unsplash-card-body">
        <div style="font-weight:800;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $photo['description'] }}</div>
        <div class="media-attribution">Photo by {{ $photo['photographer_name'] }} on Unsplash</div>
        <form action="{{ route('admin.media.unsplash.import') }}" method="POST" data-unsplash-import>
            @csrf
            <input type="hidden" name="photo_id" value="{{ $photo['id'] }}">
            <input type="hidden" name="image_url" value="{{ $photo['regular'] }}">
            <input type="hidden" name="download_location" value="{{ $photo['download_location'] }}">
            <input type="hidden" name="description" value="{{ $photo['description'] }}">
            <input type="hidden" name="photographer_name" value="{{ $photo['photographer_name'] }}">
            <input type="hidden" name="photographer_url" value="{{ $photo['photographer_url'] }}">
            <input type="hidden" name="folder" value="unsplash">
            <button class="btn btn-sm btn-primary" style="width:100%;">Import</button>
        </form>
    </div>
</div>
