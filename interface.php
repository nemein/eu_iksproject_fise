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
        $mgdschemas = midgardmvc_core::get_instance()->dispatcher->get_mgdschema_classes();
        foreach ($mgdschemas as $mgdschema)
        {
            if (   $mgdschema == 'midgardmvc_core_login_session'
                || $mgdschema == 'midgard_parameter')
            {
                continue;
            }
            midgard_object_class::connect_default($mgdschema, 'action-created', array('eu_iksproject_fise', 'store'), array());
            midgard_object_class::connect_default($mgdschema, 'action-updated', array('eu_iksproject_fise', 'store'), array());
        }
    }

    public static function object_to_text(midgard_object $object)
    {
        $text = '';
        $props = get_object_vars($object);
        $reflectionproperty = new midgard_reflection_property(get_class($object));
        foreach ($props as $property => $value)
        {
            $type = $reflectionproperty->get_midgard_type($property);
            switch ($type)
            {
                case MGD_TYPE_STRING:
                case MGD_TYPE_LONGTEXT:
                    $text .= strip_tags($object->content) . "\n\n";
                    break;
            }
        }
        return $text;
    }

    public static function store(midgard_object $object, $params)
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
