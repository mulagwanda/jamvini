<div id="mediaPickerModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.7); z-index: 1000; padding: 40px;">
    <div style="background: white; border-radius: var(--jv-radius-xl); max-width: 900px; max-height: 80vh; margin: 0 auto; overflow-y: auto;">
        <div style="padding: 20px; border-bottom: 1px solid var(--jv-gray-200); display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0;">Media Library</h3>
            <button onclick="closeMediaPicker()" style="background: none; border: none; font-size: 24px; cursor: pointer;">&times;</button>
        </div>
        <div id="mediaPickerContent" style="padding: 20px;">
            @include('plugins.Media::admin.media.picker-grid')
        </div>
    </div>
</div>
