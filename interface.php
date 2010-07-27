<?php
/**
 * @package eu_iksproject_fise
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */

/**
 * Midgard MVC administrative interface
 *
 * @package eu_iksproject_fise
 */
class eu_iksproject_fise extends midgardmvc_core_component_baseclass
{
    public function inject_process()
    {
        // Subscribe to content changed signals from Midgard so we can send content to FISE for analysis
        midgard_object_class::connect_default('midgardmvc_core_node', 'action-created', array('eu_iksproject_fise', 'store'), array());
        midgard_object_class::connect_default('midgardmvc_core_node', 'action-updated', array('eu_iksproject_fise', 'store'), array());
    }

    public static function object_to_text($object)
    {
        return strip_tags($object->content);
    }

    public static function store($object, $params)
    {
        $object_url = midgardmvc_core::get_instance()->configuration->fise_url . $object->guid;
        $ch = curl_init($object_url);
        midgardmvc_core::get_instance()->log('FISE', "Sending object to FISE at {$object_url}", 'debug');

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, eu_iksproject_fise::object_to_text($object));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/plain'));

        return curl_exec($ch);
    }
}
?>
