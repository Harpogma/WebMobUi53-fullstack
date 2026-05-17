<script setup>
import { ref, watch, onMounted, onBeforeUnmount } from 'vue';
import {
  Chart,
  BarController, BarElement,
  CategoryScale, LinearScale,
  Tooltip,
} from 'chart.js';

Chart.register(BarController, BarElement, CategoryScale, LinearScale, Tooltip);

const props = defineProps({
  options: { type: Array, required: true },
});

const canvas = ref(null);
let chart = null;

function buildChart() {
  if (chart) chart.destroy();
  chart = new Chart(canvas.value, {
    type: 'bar',
    data: {
      labels: props.options.map(o => o.label),
      datasets: [{
        data: props.options.map(o => o.votes_count ?? 0),
        backgroundColor: 'rgba(25, 118, 210, 0.75)',
        borderRadius: 6,
      }],
    },
    options: {
      indexAxis: 'y',
      responsive: true,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: ctx => `${ctx.raw} vote${ctx.raw !== 1 ? 's' : ''}`,
          },
        },
      },
      scales: {
        x: {
          beginAtZero: true,
          ticks: { precision: 0 },
          grid: { color: 'rgba(0,0,0,0.05)' },
        },
        y: {
          grid: { display: false },
        },
      },
    },
  });
}

onMounted(buildChart);
watch(() => props.options, buildChart, { deep: true });
onBeforeUnmount(() => { if (chart) chart.destroy(); });
</script>

<template>
  <canvas ref="canvas" />
</template>
