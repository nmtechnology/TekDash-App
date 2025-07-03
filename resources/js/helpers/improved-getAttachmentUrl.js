/**
 * Get a proper URL for an attachment object or string
 * @param {Object|string} attachment - The attachment to get a URL for
 * @returns {string} - The URL for the attachment
 */
export function getAttachmentUrl(attachment) {
  if (!attachment) return '';

  try {
    // Get the current origin to ensure consistent URLs
    const origin = window.location.origin;
    
    // Debug info to help diagnose URL issues
    console.log('AttachmentHelper: Getting URL for:', attachment);
    
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
      } 
      // Handle storage URLs specifically
      else if (attachment.includes('storage/')) {
        // Ensure consistent storage path formatting
        const storagePath = attachment.includes('/storage/') ? attachment : `/storage/${attachment.replace(/^storage\//, '')}`;
        result = `${origin}${storagePath}`;
      } 
      // Handle other relative URLs
      else {
        result = `${origin}${attachment.startsWith('/') ? '' : '/'}${attachment}`;
      }
    } else if (attachment.url) {
      // If it's already a full URL, return as is or fix localhost
      if (attachment.url.startsWith('http://') || attachment.url.startsWith('https://')) {
        if (attachment.url.includes('localhost') && !origin.includes('localhost')) {
          result = attachment.url.replace(/http:\/\/localhost(?:\:\d+)?/, origin);
        } else {
          result = attachment.url;
        }
      } 
      // Handle storage URLs within attachment.url
      else if (attachment.url.includes('storage/')) {
        // Ensure URL has proper storage path formatting
        const storagePath = attachment.url.includes('/storage/') ? attachment.url : `/storage/${attachment.url.replace(/^storage\//, '')}`;
        result = `${origin}${storagePath}`;
      }
      // Handle other relative URLs
      else {
        result = `${origin}${attachment.url.startsWith('/') ? '' : '/'}${attachment.url}`;
      }
    } else if (attachment.path) {
      // Better handling of storage paths from path property
      const path = attachment.path.startsWith('/storage/') 
        ? attachment.path 
        : `/storage/${attachment.path.replace(/^public[\/]/, '').replace(/^storage\//, '')}`;
      result = `${origin}${path}`;
    } else if (attachment.file_name) {
      // Better handling of storage paths from file_name property
      const path = attachment.file_name.startsWith('/storage/') 
        ? attachment.file_name 
        : `/storage/${attachment.file_name.replace(/^public[\/]/, '').replace(/^storage\//, '')}`;
      result = `${origin}${path}`;
    }
    
    // Verify the URL is properly formed
    try {
      new URL(result);
    } catch (e) {
      // Try to fix the URL by forcing it to be absolute
      if (result && !result.startsWith('http')) {
        result = `${origin}${result.startsWith('/') ? '' : '/'}${result}`;
      }
    }
    
    console.log('AttachmentHelper: Final URL generated:', result);
    return result;
  } catch (e) {
    console.error('Error getting attachment URL:', e);
    return '';
  }
}
