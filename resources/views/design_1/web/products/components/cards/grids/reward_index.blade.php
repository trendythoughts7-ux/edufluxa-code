@push('styles_top')
    @if(empty($withoutStyles))
        <link rel="stylesheet" href="{{ getDesign1StylePath("product_cards/grid_card_1") }}">
    @endif
@endpush

@foreach($products as $product)
    <div class="{{ !empty($gridCardClassName) ? $gridCardClassName : '' }}">
        @include("design_1.web.products.components.cards.grids.grid_card_reward", ['product' => $product])
    </div>
@endforeach
