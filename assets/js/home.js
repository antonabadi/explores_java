(function () {
  /* Carousel destinations */
  var track = document.getElementById('destTrack');
  function step() { var c = track.querySelector('.dest-card'); return c ? c.offsetWidth + 20 : 280; }
  document.getElementById('carPrev').addEventListener('click', function () { track.scrollBy({ left: -step(), behavior: 'smooth' }); });
  document.getElementById('carNext').addEventListener('click', function () { track.scrollBy({ left: step(), behavior: 'smooth' }); });

  document.getElementById('searchForm').addEventListener('submit', function (e) {
    e.preventDefault(); showToast('Mencari perjalanan terbaik… (demo)');
  });
  document.getElementById('watchVideo').addEventListener('click', function () {
    showToast('Video segera hadir (demo)');
  });
})();
