@props(['dataModel' => null, 'height' => 300, 'toolbar' => null, 'menubar' => 'true'])
<div wire:ignore class="mt-2 text-black w-full" x-data="tinyEditor('{{ $dataModel }}', {{ $height }}, '{{ $toolbar }}', {{ $menubar }})">
    <textarea x-ref="textarea" class="w-full border rounded-md"></textarea>
</div>