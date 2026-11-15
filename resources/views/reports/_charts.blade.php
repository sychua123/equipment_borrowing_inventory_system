{{-- Author: Lai Chee Kit · Module 4: draws charts with Chart.js --}}
{{-- Needs: $charts (list of type, title, labels, values) and $prefix (unique text for the canvas IDs) --}}

@foreach ($charts as $index => $chart)
  <div class="chart">
    <canvas id="{{ $prefix }}-chart-{{ $index }}" role="img" aria-label="{{ $chart['title'] }}"></canvas>
  </div>
@endforeach

@once
  {{-- Chart.js is stored in our own public/js folder, so it works without internet --}}
  <script src="{{ asset('js/chart.umd.min.js') }}"></script>
@endonce

<script>
  // The json directive turns the PHP array into safe JavaScript data
  (function () {
    const charts = @json($charts);
    const colours = ['#1764c0', '#e0902f', '#2f9e6b', '#8a8f98', '#a23fb5', '#c0271d'];

    charts.forEach(function (chart, index) {
      new Chart(document.getElementById(@json($prefix) + '-chart-' + index), {
        type: chart.type,
        data: {
          labels: chart.labels,
          datasets: [{
            label: chart.title,
            data: chart.values,
            backgroundColor: chart.type === 'bar' ? colours[0] : colours,
          }],
        },
        options: {
          plugins: {
            title: { display: true, text: chart.title },
            legend: { display: chart.type !== 'bar' },
          },
          scales: chart.type === 'bar' ? { y: { beginAtZero: true, ticks: { precision: 0 } } } : {},
        },
      });
    });
  })();
</script>
