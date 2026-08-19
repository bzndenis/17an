<div {{ $attributes->merge(['class' => 'table-container']) }}>
    <table class="data-table">
        {{ $slot }}
    </table>
</div>
