(function () {
  // Define breakpoints for mobile, tablet, and desktop
  const mobileBreakpoint = 767;
  const tabletBreakpoint = 1024;

  let previousDeviceCategory = ""; // To store the previous window size category

  // Function to detect device size category
  function getDeviceCategory() {
    const width = window.innerWidth;

    if (width <= mobileBreakpoint) {
      return "mobile";
    } else if (width > mobileBreakpoint && width <= tabletBreakpoint) {
      return "tablet";
    } else {
      return "desktop";
    }
  }

  // Function to check for window resize and refresh if needed
  function checkWindowSize() {
    const currentDeviceCategory = getDeviceCategory();

    // Compare with the previous device category
    if (currentDeviceCategory !== previousDeviceCategory) {
      previousDeviceCategory = currentDeviceCategory; // Update the previous category
      location.reload();
    }
  }

  document.addEventListener("DOMContentLoaded", function () {
    let resizeTimeout;

    function checkWindowSizeDebounced() {
      clearTimeout(resizeTimeout);
      resizeTimeout = setTimeout(checkWindowSize, 200); // 200ms delay
    }

    // Initial detection when the page loads
    previousDeviceCategory = getDeviceCategory();

    window.addEventListener("resize", checkWindowSizeDebounced);
  });
})();
