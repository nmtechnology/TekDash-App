<template>
    <div class="logo-container">
      <img src="https://www.nmtechnology.us/build/assets/nm-logo-rmbg-f8bd446d.webp" alt="nmtis-logo" class="animated-logo">
    </div>
  </template>
  
  <style scoped>
  .logo-container {
    display: flex;
    justify-content: center;
    align-items: center;
    perspective: 1000px; /* Add perspective for 3D effect */
  }
  
  .animated-logo {
    width: 65px; /* Adjust the size as needed */
    height: 35px; /* Adjust the size as needed */
    animation: spin-pause 15s linear infinite; /* Apply the spin-pause animation */
    transform-style: preserve-3d; /* Preserve 3D transformations */
  }
  
  @keyframes spin-pause {
    0%, 40% {
      transform: rotateY(0deg); /* Pause for the first 20 seconds (40% of 50s) */
    }
    100% {
      transform: rotateY(360deg); /* Spin for the remaining 30 seconds */
    }
  }
  </style>