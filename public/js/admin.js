let activeFilters = {
  medical: true,
  fire: true,
  assault: true,
  accident: true
};

document.addEventListener('DOMContentLoaded', () => {
  initializeMap();        
  setupEventListeners();  
  updateTotalCount();     
});

function setupEventListeners() {
  
  document.querySelectorAll('.incident-item').forEach(item => {
    item.addEventListener('click', () => {
      const type = item.dataset.type;
      activeFilters[type] = !activeFilters[type];
      item.classList.toggle('is-off', !activeFilters[type]);

      updateMapWithFilters();
      updateTotalCount();
    });
  });

  
  const searchInput = document.querySelector('.search-input');
  searchInput.addEventListener('keypress', (e) => {
    if (e.key === 'Enter') performSearch(searchInput.value);
  });

  
  const btnCenter = document.getElementById('btnCenter');
  btnCenter?.addEventListener('click', () => {
    
    console.log('Centrar en Riobamba');
  });
}
