/**
 * Created by admin on 6/25/2019.
 */

function countryChange(selectedObject)
{
    jQuery('#tobDomain').val(jQuery(selectedObject.options[selectedObject.selectedIndex]).attr('data-topDomain'));
    jQuery('#alpha2').val(jQuery(selectedObject.options[selectedObject.selectedIndex]).attr('data-alpha2Code'));
    jQuery('#alpha3').val(jQuery(selectedObject.options[selectedObject.selectedIndex]).attr('data-alpha3Code'));
    jQuery('#callCode').val(jQuery(selectedObject.options[selectedObject.selectedIndex]).attr('data-callCode'));
    jQuery('#zone').val(jQuery(selectedObject.options[selectedObject.selectedIndex]).attr('data-zone'));
    jQuery('#currency').val(jQuery(selectedObject.options[selectedObject.selectedIndex]).attr('data-currency'));
    jQuery('#flag').val(jQuery(selectedObject.options[selectedObject.selectedIndex]).attr('data-flag'));
}
