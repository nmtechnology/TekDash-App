// Helper: Get the full URL for an attachment
function getAttachmentUrl(attachment) {
  if (!attachment) return '';
  
  // Debug information
  console.log('WorkOrder: Getting attachment URL for:', attachment);
  
  // Get the current origin to ensure consistent URLs
  const origin = window.location.origin;
  
  let result = '';
  
  if (typeof attachment === 'string') {
    // If it's already a full URL, return as is
    if (attachment.startsWith('http://') || attachment.startsWith('https://')) {
      // Replace localhost with current origin if needed
      if (attachment.includes('localhost') && !origin.includes('localhost')) {
        result = attachment.replace(/http:\/\/localhost(?:\:\d+)?/, origin);
      } else {
        result = attachment;
      }
    } else {
      // Handle storage path properly
      if (attachment.includes('storage/')) {
        const storagePath = attachment.includes('/storage/') ? 
          attachment : 
          `/storage/${attachment.replace(/^storage\//, '')}`;
        result = `${origin}${storagePath}`;
      } else {
        // Other relative paths
        result = `${origin}${attachment.startsWith('/') ? '' : '/'}${attachment}`;
      }
    }
  } else if (attachment.url) {
    // If URL is already a full URL, return as is or fix localhost
    if (attachment.url.startsWith('http://') || attachment.url.startsWith('https://')) {
      if (attachment.url.includes('localhost') && !origin.includes('localhost')) {
        result = attachment.url.replace(/http:\/\/localhost(?:\:\d+)?/, origin);
      } else {
        result = attachment.url;
      }
    } else {
      // Handle storage path properly for URLs
      if (attachment.url.includes('storage/')) {
        const storagePath = attachment.url.includes('/storage/') ? 
          attachment.url : 
          `/storage/${attachment.url.replace(/^storage\//, '')}`;
        result = `${origin}${storagePath}`;
      } else {
        // Other relative paths
        result = `${origin}${attachment.url.startsWith('/') ? '' : '/'}${attachment.url}`;
      }
    }
  } else if (attachment.path) {
    // Ensure consistent storage path format
    const path = attachment.path.includes('/storage/') ? 
      attachment.path : 
      `/storage/${attachment.path.replace(/^(public\/|storage\/|public|public\/storage\/)?/, '')}`;
    result = `${origin}${path}`;
  } else if (attachment.file_name) {
    // Ensure consistent storage path format
    const path = attachment.file_name.includes('/storage/') ?
      attachment.file_name :
      `/storage/${attachment.file_name.replace(/^(public\/|storage\/|public|public\/storage\/)?/, '')}`;
    result = `${origin}${path}`;
  }
  
  console.log('WorkOrder: Final attachment URL:', result);
  return result;
}
