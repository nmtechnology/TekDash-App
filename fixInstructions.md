# Fix Instructions for AddWorkOrder.vue

## Issue 1: Undefined Properties (mapboxImageUrl)

The first issue is that the `mapboxImageUrl` property is being used in the template but isn't defined properly as a computed property. Make sure this computed property is defined:

```javascript
const mapboxImageUrl = computed(() => {
  if (!mapboxCoords.value.lat || !mapboxCoords.value.lon) return null;
  
  const lat = mapboxCoords.value.lat;
  const lon = mapboxCoords.value.lon;
  const zoom = 14;
  const width = 600;
  const height = 300;
  const marker = `pin-l-circle+ff4400(${lon},${lat})`;
  
  return `https://api.mapbox.com/styles/v1/mapbox/streets-v11/static/${marker}/${lon},${lat},${zoom},0/${width}x${height}@2x?access_token=${mapboxAccessToken}`;
});

const mapboxMapsLink = computed(() => {
  if (!mapboxCoords.value.lat || !mapboxCoords.value.lon) return '#';
  return `https://www.google.com/maps/search/?api=1&query=${mapboxCoords.value.lat},${mapboxCoords.value.lon}`;
});
```

## Issue 2: CSRF Token for 419 Errors

Ensure the CSRF token is included in all Axios requests by adding this code to the top of your script section:

```javascript
// Set up Axios to include CSRF token
axios.defaults.headers.common['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
axios.defaults.withCredentials = true;
```

## Issue 3: QR Code and Technician Info in Step 10

Add these computed properties to ensure the technician and customer info displays correctly:

```javascript
const qrCodeValue = computed(() => {
  return 'TekDash:' + (formattedTitle.value || '') + (form.address ? '|' + form.address : '') + (formattedDateTime.value ? '|' + formattedDateTime.value : '');
});

const selectedTechnicianName = computed(() => {
  const tech = safeTechniciansArray.value.find(t => t.id == form.technician_id);
  if (!tech) return 'None selected';
  if (tech.first_name && tech.last_name) {
    return tech.first_name + ' ' + tech.last_name + (tech.employee_id ? ' (' + tech.employee_id + ')' : '');
  }
  return tech.name || 'Technician #' + tech.id;
});

const selectedCustomerName = computed(() => {
  const customer = safeCustomersArray.value.find(c => c.id == form.customer_id);
  return customer ? (customer.business_name || customer.name || `Customer #${customer.id}`) : 'None selected';
});
```

## Issue 4: Fix the printWorkOrder function

Update the printWorkOrder function to use the computed properties:

```javascript
const printWorkOrder = () => {
  try {
    // Create a printable version that only includes the Step 10 content
    const printWindow = window.open('', '_blank');
    if (!printWindow) {
      alert('Please allow pop-up windows to print the work order');
      return;
    }

    // Use computed properties directly
    const customerName = selectedCustomerName.value;
    const technicianName = selectedTechnicianName.value;
    const qrValue = qrCodeValue.value;

    // Function continues...
```

## Issue 5: Implementation of geocodeAddress function

Ensure you have the geocodeAddress function properly implemented:

```javascript
const geocodeAddress = async (address) => {
  if (!address || address.trim() === '') {
    mapboxCoords.value = { lat: null, lon: null };
    mapboxError.value = null;
    return;
  }

  try {
    mapboxLoading.value = true;
    mapboxError.value = null;
    
    const encodedAddress = encodeURIComponent(address);
    const url = `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodedAddress}.json?access_token=${mapboxAccessToken}&limit=1`;
    
    const response = await axios.get(url);
    
    if (response.data.features && response.data.features.length > 0) {
      const feature = response.data.features[0];
      const [lon, lat] = feature.center;
      
      mapboxCoords.value = { lat, lon };
    } else {
      mapboxError.value = 'Address not found. Please try a more specific address.';
      mapboxCoords.value = { lat: null, lon: null };
    }
  } catch (error) {
    console.error('Geocoding error:', error);
    mapboxError.value = 'Error finding address. Please try again.';
    mapboxCoords.value = { lat: null, lon: null };
  } finally {
    mapboxLoading.value = false;
  }
};
```

## Issue 6: Duplicate Declarations

**Important**: The current file has many duplicate declarations. You need to remove all duplicate definitions of the following:

- `mapboxImageUrl`
- `mapboxMapsLink`
- `printWorkOrder`
- `formattedTitle`
- `laborCost`
- `travelCost`
- `totalPrice`
- `currentMonthName`
- `selectedDateFormatted`
- `formattedDateTime`
- `descriptionSummary`
- `safeCustomersArray`
- `safeTechniciansArray`
- `resetForm`
- `validateCurrentStep`
- `nextStep`
- `prevStep`

Make sure each of these is declared **only once** in the file.
