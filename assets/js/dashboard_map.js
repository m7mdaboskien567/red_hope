let dashboardMap = null;
let mapInitialized = false;

document.addEventListener("sectionLoaded", (e) => {
  if (e.detail.section === "maps" && !mapInitialized) {
    initDashboardMap();
  } else if (e.detail.section === "maps" && dashboardMap) {
    setTimeout(() => {
      dashboardMap.invalidateSize();
    }, 100);
  }
});

document.addEventListener("DOMContentLoaded", () => {
  const urlParams = new URLSearchParams(window.location.search);
  const tab = urlParams.get("tab");
  if (tab === "maps") {
    initDashboardMap();
  }
});

async function initDashboardMap() {
  const mapEl = document.getElementById("dashboardMap");
  if (!mapEl || mapInitialized) return;

  dashboardMap = L.map("dashboardMap", {
    center: [26.8206, 30.8025],
    zoom: 6,
    scrollWheelZoom: false,
    zoomControl: true,
  });

  L.tileLayer(
    "https:
    {
      attribution:
        '&copy; <a href="https:
      maxZoom: 19,
    },
  ).addTo(dashboardMap);

  mapInitialized = true;

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
        <a href="https:
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

      markers.addTo(dashboardMap);

      if (data.centers.length > 0) {
        dashboardMap.fitBounds(markers.getBounds().pad(0.1));
      }
      setTimeout(() => {
        dashboardMap.invalidateSize();
      }, 200);

      console.log(`🗺️ Dashboard Map loaded: ${data.centers.length} centers`);
    }
  } catch (e) {
    console.error("Map Error:", e);
    mapEl.innerHTML =
      '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:var(--text-muted)">Could not load map data</div>';
  }
}
