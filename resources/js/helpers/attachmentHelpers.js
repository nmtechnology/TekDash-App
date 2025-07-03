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
    
    // Debug log to see what attachment is being processed
    const attachmentName = typeof attachment === 'string' ? attachment : 
      attachment.file_name || attachment.name || attachment.url || 'Unknown';
    console.log('AttachmentHelper: Getting URL for attachment:', attachmentName);
    
    // If the attachment is already a URL object, convert it to string
    if (attachment instanceof URL) {
      return attachment.toString();
    }
    
    // Cache result value
    let result = '';
    
    // If the attachment has a cached _previewUrl, use it
    if (attachment && attachment._previewUrl) {
      console.log('AttachmentHelper: Using cached preview URL');
      return attachment._previewUrl;
    }
    
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
    
    console.log('AttachmentHelper: Final URL generated:', result);
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
  if (!attachment) {
    console.log('AttachmentHelper: isPdfFile received null/undefined input');
    return false;
  }
  
  try {
    const attachmentType = typeof attachment;
    console.log(`AttachmentHelper: Checking if file is PDF (type: ${attachmentType}):`, 
      attachmentType === 'string' ? attachment : 
      (attachment.file_name || attachment.name || 'object'));
    
    // For string attachments, check if it's a path to a PDF
    if (attachmentType === 'string') {
      const isPdf = attachment.toLowerCase().endsWith('.pdf');
      console.log(`AttachmentHelper: String path check result: ${isPdf ? 'IS PDF' : 'not PDF'}`);
      return isPdf;
    }
    
    // Check all possible type-related properties
    const typeProperties = ['file_type', 'mime_type', 'type', 'content_type', 'mime'];
    for (const prop of typeProperties) {
      if (attachment[prop] && attachment[prop].toLowerCase().includes('pdf')) {
        console.log(`AttachmentHelper: PDF detected via ${prop}: ${attachment[prop]}`);
        return true;
      }
    }
    
    // Check all possible name/path properties for .pdf extension
    const nameProperties = ['file_name', 'name', 'path', 'url', 'filename', 'original_name'];
    for (const prop of nameProperties) {
      if (attachment[prop] && typeof attachment[prop] === 'string' && 
          attachment[prop].toLowerCase().endsWith('.pdf')) {
        console.log(`AttachmentHelper: PDF detected via ${prop} extension: ${attachment[prop]}`);
        return true;
      }
    }
    
    // Special case: check if there's an _isPdf flag already set
    if (attachment._isPdf === true) {
      console.log('AttachmentHelper: PDF detected via pre-set _isPdf flag');
      return true;
    }
    
    // If we have attachments as nested properties, recurse
    if (attachment.attachment && typeof attachment.attachment === 'object') {
      const isPdf = isPdfFile(attachment.attachment);
      if (isPdf) {
        console.log('AttachmentHelper: PDF detected via nested attachment property');
        return true;
      }
    }
    
    // For File objects from browser uploads
    if (attachment instanceof File) {
      const isPdf = attachment.type === 'application/pdf' || 
                    attachment.name?.toLowerCase().endsWith('.pdf');
      console.log(`AttachmentHelper: File object check result: ${isPdf ? 'IS PDF' : 'not PDF'}`);
      return isPdf;
    }
    
    console.log('AttachmentHelper: Not a PDF file after all checks');
    return false;
  } catch (e) {
    console.error('Error checking if file is PDF:', e);
    return false;
  }
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
  // Handle empty or invalid inputs gracefully
  if (!attachments) {
    console.log('AttachmentHelper: processAttachments received null/undefined input');
    return [];
  }
  
  // If a single attachment is passed (not in an array), convert it to an array
  if (!Array.isArray(attachments)) {
    console.log('AttachmentHelper: processAttachments received non-array, converting:', attachments);
    
    // If it's a string that might be JSON, try to parse it
    if (typeof attachments === 'string' && attachments.trim().startsWith('[')) {
      try {
        const parsed = JSON.parse(attachments);
        if (Array.isArray(parsed)) {
          console.log('AttachmentHelper: Successfully parsed JSON string to array');
          attachments = parsed;
        }
      } catch (e) {
        console.log('AttachmentHelper: Failed to parse string as JSON');
      }
    }
    
    // If still not an array, wrap in array
    if (!Array.isArray(attachments)) {
      attachments = attachments ? [attachments] : [];
    }
  }
  
  try {
    console.log(`AttachmentHelper: Processing ${attachments.length} attachments`);
    
    const processed = attachments
      .filter(att => att !== null && att !== undefined)
      .map(att => {
        // Generate and cache key properties for each attachment
        const url = getAttachmentUrl(att);
        const isPdf = isPdfFile(att);
        const isImg = isImageFile(att);
        const fileName = getFileName(att);
        
        console.log(`AttachmentHelper: Processed attachment ${fileName} - isPDF: ${isPdf}, isImage: ${isImg}, url: ${url}`);
        
        // Start with original attachment
        const processedAtt = { ...att };
        
        // Ensure consistent property names across all attachments
        processedAtt.url = processedAtt.url || url;
        processedAtt.file_name = processedAtt.file_name || processedAtt.name || fileName;
        
        // Normalize path property
        if (!processedAtt.path && processedAtt.url) {
          const urlObj = new URL(processedAtt.url);
          processedAtt.path = urlObj.pathname;
        }
        
        // Add metadata flags for consistent checking
        processedAtt._previewUrl = url;
        processedAtt._isPdf = isPdf;
        processedAtt._isImage = isImg;
        processedAtt._fileName = fileName;
        
        // Add ID if missing (helps with keying in v-for loops)
        if (!processedAtt.id && !processedAtt._id) {
          processedAtt._id = Math.random().toString(36).substring(2, 15);
        }
        
        return processedAtt;
      });
    
    console.log(`AttachmentHelper: Returning ${processed.length} processed attachments`);
    return processed;
  } catch (e) {
    console.error('AttachmentHelper: Error processing attachments:', e);
    return [];
  }
}
