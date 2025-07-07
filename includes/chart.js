document.addEventListener('DOMContentLoaded', () => {
  fetch('mood_chart.php')
    .then(res => res.json())
    .then(data => {
      new Chart(document.getElementById('moodChart'), {
        type: 'bar',
        data: {
          labels: data.labels,
          datasets: [{
            label: 'Mood Count',
            data: data.counts,
            backgroundColor: ['#4caf50', '#ffeb3b', '#f44336']
          }]
        }
      });
    });
});
