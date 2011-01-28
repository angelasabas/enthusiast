function checkAll( checkname, exby, boxname ) {
   for( i = 0; i < checkname.elements.length; i++ )
      if( checkname.elements[i].type == 'checkbox' &&
         boxname == checkname.elements[i].name ) {
         checkname.elements[i].checked = exby.checked ? true :false
      }
}

var counter = 1;
function moreFields() {
// if multifields is present
   if( document.getElementById( 'multifields' ) == null )
      return false;
   counter++;
   var newFields = document.getElementById( 'multifields' ).cloneNode( true );
   newFields.id = '';
   newFields.style.display = 'block';
   var newField = newFields.childNodes;
   for( var i = 0; i < newField.length; i++ ) {
      var theName = newField[i].name;
      if( theName )
         newField[i].name = theName + counter;
   }
   var insertHere = document.getElementById( 'multifieldshere' );
   insertHere.parentNode.insertBefore(newFields,insertHere);
}