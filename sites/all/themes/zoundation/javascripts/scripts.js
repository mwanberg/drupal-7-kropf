(function ($) {
 
 // Adds Invalid Entry message onto form fields with .error
 $('input.error, textarea.error').after('<small class="error">Invalid entry</small>');
 

  // RESPONSIVE TABLES
  // Adds .responsive onto all tables except tables emberdded within a form
  // Many of form table elements use javascript to inititate dragging which
  // does not work in smaller media queries when responsive-tables.js has fired
  
  // Uncomment line below to turn on responsive tables
  //$('table').not('form table').addClass('responsive');

})(jQuery);