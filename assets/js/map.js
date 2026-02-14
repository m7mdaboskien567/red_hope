document.addEventListener("DOMContentLoaded", async () => {
  const mapEl = document.getElementById("bloodCentersMap");
  if (!mapEl) return;

  const map = L.map("bloodCentersMap", {
    center: [29.8, 31.3],
    zoom: 6,
    scrollWheelZoom: false,
    zoomControl: true,
  });

  L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
    attribution:
      '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    maxZoom: 19,
  }).addTo(map);

  function createMarkerIcon() {
    return L.divIcon({
      html: '<div class="custom-marker"><i class="bi bi-droplet-fill"></i></div>',
      className: "custom-marker-wrapper",
      iconSize: [36, 36],
      iconAnchor: [18, 36],
      popupAnchor: [0, -36],
    });
  }

  function createPopup(center) {
    return `
      <div class="map-popup">
        <div class="popup-header">
          <i class="bi bi-hospital"></i>
          <h4>${center.name}</h4>
        </div>
        <div class="popup-detail">
          <i class="bi bi-geo-alt"></i>
          <span>${center.address}, ${center.city}</span>
        </div>
        <div class="popup-detail">
          <i class="bi bi-telephone"></i>
          <span>${center.contact_number}</span>
        </div>
        <a href="https://www.google.com/maps/dir/?api=1&destination=${center.lat},${center.lng}" 
           target="_blank" class="popup-btn">
          <i class="bi bi-sign-turn-right"></i> Get Directions
        </a>
      </div>
    `;
  }

  try {
    const res = await fetch("/redhope/apis/get_map_centers.php");
    const data = await res.json();

    if (data.success && data.centers) {
      const markers = L.featureGroup();

      data.centers.forEach((center, index) => {
        const marker = L.marker([center.lat, center.lng], {
          icon: createMarkerIcon(),
        });
        marker.bindPopup(createPopup(center), {
          maxWidth: 280,
          closeButton: true,
        });
        markers.addLayer(marker);
      });

      markers.addTo(map);

      if (data.centers.length > 0) {
        map.fitBounds(markers.getBounds().pad(0.15));
      }
      const countEl = document.getElementById("mapCenterCount");
      if (countEl) countEl.textContent = data.centers.length;

      const cityCount = [...new Set(data.centers.map((c) => c.city))].length;
      const cityEl = document.getElementById("mapCityCount");
      if (cityEl) cityEl.textContent = cityCount;

      console.log(
        `🗺️ Map loaded: ${data.centers.length} centers in ${cityCount} cities`,
      );
    }
  } catch (e) {
    console.error("Map Error:", e);
    mapEl.innerHTML =
      '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--text-muted)">Could not load map data</div>';
  }
});
