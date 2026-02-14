/**
 * Location Service for RedHope
 * Handles Geolocation and distance calculations using the Haversine formula.
 */

const LocationService = {
  /**
   * Get current user position
   * @returns {Promise<{lat: number, lng: number}>}
   */
  getCurrentPosition: function () {
    return new Promise((resolve, reject) => {
      if (!navigator.geolocation) {
        reject(new Error("Geolocation is not supported by your browser"));
        return;
      }

      navigator.geolocation.getCurrentPosition(
        (position) => {
          resolve({
            lat: position.coords.latitude,
            lng: position.coords.longitude,
          });
        },
        (error) => {
          reject(error);
        },
        {
          enableHighAccuracy: true,
          timeout: 5000,
          maximumAge: 0,
        },
      );
    });
  },

  /**
   * Calculate distance between two points in KM
   * @param {number} lat1
   * @param {number} lon1
   * @param {number} lat2
   * @param {number} lon2
   * @returns {number}
   */
  calculateDistance: function (lat1, lon1, lat2, lon2) {
    const R = 6371; // Radius of the earth in km
    const dLat = this.deg2rad(lat2 - lat1);
    const dLon = this.deg2rad(lon2 - lon1);
    const a =
      Math.sin(dLat / 2) * Math.sin(dLat / 2) +
      Math.cos(this.deg2rad(lat1)) *
        Math.cos(this.deg2rad(lat2)) *
        Math.sin(dLon / 2) *
        Math.sin(dLon / 2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    const d = R * c; // Distance in km
    return d;
  },

  deg2rad: function (deg) {
    return deg * (Math.PI / 180);
  },

  /**
   * Sort centers by proximity to user
   * @param {Array} centers
   * @param {{lat: number, lng: number}} userCoords
   * @returns {Array} Centers with 'distance' property, sorted
   */
  sortCentersByProximity: function (centers, userCoords) {
    return centers
      .map((center) => {
        const dist = this.calculateDistance(
          userCoords.lat,
          userCoords.lng,
          center.lat,
          center.lng,
        );
        return { ...center, distance: dist };
      })
      .sort((a, b) => a.distance - b.distance);
  },
};

window.LocationService = LocationService;
