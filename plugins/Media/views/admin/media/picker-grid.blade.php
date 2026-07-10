<div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 12px;">
    @foreach($media as $file)
    <div style="border: 1px solid var(--jv-gray-200); border-radius: var(--jv-radius-sm); overflow: hidden; cursor: pointer; text-align: center;"
         onclick="selectMedia('{{ $file->url }}', '{{ $file->id }}')"
         ondblclick="insertMedia('{{ $file->url }}')">
        @if($file->is_image)
            <img src="{{ $file->thumbnail_url }}" style="width: 100%; height: 100px; object-fit: cover;">
        @else
            <div style="height: 100px; display: flex; align-items: center; justify-content: center; font-size: 36px;">📄</div>
        @endif
        <div style="padding: 6px; font-size: 0.65rem; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
            {{ $file->original_name }}
        </div>
    </div>
    @endforeach
</div>
{{ $media->links() }}