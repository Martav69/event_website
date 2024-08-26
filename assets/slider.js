document.addEventListener('DOMContentLoaded', function() {
    console.log("initializing slider");

    const params = new URLSearchParams(window.location.search);
    const minPrice = params.get('minPrice');
    const maxPrice = params.get('maxPrice');
    console.log("prices : ", minPrice, maxPrice);

    let slider = new RangeSlider('#price-range-slider', {
        values: [minPrice ?? 0, maxPrice ?? 300],
        step: 10,
        min: 0,
        max: 300,
        colors: {
            points: "#4F46E5",
            rail: "#E5E7EB",
            tracks: "#4F46E5"
        },
        pointRadius: 10,
        railHeight: 4,
        trackHeight: 4
    });

    slider.onChange(values => {
        document.getElementById('minPrice').value = values[0];
        document.getElementById('maxPrice').value = values[1];
        document.getElementById('min-price-display').textContent = values[0];
        document.getElementById('max-price-display').textContent = values[1];
    });
});