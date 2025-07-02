/**
 * Attachment helper functions for handling attachments in the TekDash app
 */

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
        // Ensure URL has proper storage path formatting
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
    
    return result;
  } catch (e) {
    console.error('Error getting attachment URL:', e);
    return '';
  }
}

/**
 * Check if an attachment is a PDF file
 * @param {Object|string} attachment - The attachment to check
 * @returns {boolean} - True if the attachment is a PDF file
 */
export function isPdfFile(attachment) {
  if (!attachment) return false;
  
  try {
    // First check file_type or mime_type
    if (attachment.file_type && attachment.file_type.includes('pdf')) {
      return true;
    }
    if (attachment.mime_type && attachment.mime_type.includes('pdf')) {
      return true;
    }
    
    // Then check filename extensions in various properties
    const nameToCheck = attachment.file_name || attachment.name || attachment.url || '';
    if (nameToCheck.toLowerCase().endsWith('.pdf')) {
      return true;
    }
    
    // Check path property if available
    if (attachment.path && attachment.path.toLowerCase().endsWith('.pdf')) {
      return true;
    }
    
    // For string attachments, check if it's a path to a PDF
    if (typeof attachment === 'string' && attachment.toLowerCase().endsWith('.pdf')) {
      return true;
    }
  } catch (e) {
    console.error('Error checking if file is PDF:', e);
  }
  
  return false;
}

/**
 * Check if an attachment is an image file
 * @param {Object|string} attachment - The attachment to check
 * @returns {boolean} - True if the attachment is an image file
 */
export function isImageFile(attachment) {
  if (!attachment) return false;

  try {
    // Check if attachment has file_type property
    if (attachment.file_type) {
      return attachment.file_type.startsWith('image/');
    }
    
    // Check file name extension as fallback
    const name = attachment.file_name || attachment.name || attachment.url || '';
    const ext = name.toLowerCase().split('.').pop();
    return ['jpg', 'jpeg', 'png', 'gif', 'heic'].includes(ext);
  } catch (e) {
    console.error('Error checking if file is image:', e);
    return false;
  }
}

/**
 * Get a descriptive filename from an attachment
 * @param {Object|string} attachment - The attachment to get a filename for
 * @returns {string} - The filename or a descriptive string
 */
export function getFileName(attachment) {
  if (!attachment) return 'Unknown file';
  
  try {
    if (typeof attachment === 'string') {
      // If attachment is a URL string, extract the filename
      const parts = attachment.split('/');
      return parts[parts.length - 1];
    }
    
    // Try various properties that might contain the filename
    return attachment.file_name || attachment.name || attachment.original_name || 
           (attachment.path ? attachment.path.split('/').pop() : 'File');
  } catch (e) {
    console.error('Error getting filename:', e);
    return 'File';
  }
}

/**
 * Process an array of attachments to add preview URLs and type flags
 * @param {Array} attachments - The attachments to process
 * @returns {Array} - The processed attachments
 */
export function processAttachments(attachments) {
  if (!Array.isArray(attachments)) return [];
  
  try {
    return attachments.map(att => {
      if (!att) return null;
      
      const url = getAttachmentUrl(att);
      const isPdf = isPdfFile(att);
      
      return {
        ...att,
        _previewUrl: url,
        _isPdf: isPdf,
        _isImage: isImageFile(att),
        _fileName: getFileName(att)
      };
    }).filter(Boolean); // Remove any null items
  } catch (e) {
    console.error('Error processing attachments:', e);
    return [];
  }
}
