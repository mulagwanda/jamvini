@include('themes.default::blocks.render', ['block' => $block, 'data' => $data ?? ($block['data'] ?? [])])
