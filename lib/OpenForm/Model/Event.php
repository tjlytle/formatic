<?php
namespace OpenForm\Model;

class Event
{
    protected $data;
    
    public function __construct($data)
    {
        $this->data = $data;
    }
    
    public function genFDF()
    {
        $flat = $this->flatten($this->data['specialEventApplication']);
        $fdf = array();
        
        $simpleChecks = array(
        	'Open Burn' => 'event.setUp.openBurn',
        	'Fireworks' => 'event.setUp.fireworks',
        	'Tents may not be in street or staked into' => 'event.setUp.tents',
        	'Public Property' => 'event.location.type.publicProp',
        	'Public Right of Way sidewalk' => 'event.location.type.publicRightOfWay',
        	'Private Property' => 'event.location.type.privateProp',
        	'Hydrant use Adapter Appendix Water' => 'otherServices.hydrant',
        	'Temp Structure Appendix Code Enforcement' => 'otherServices.tempStructure',
        	'Clean up Plan attached Appendix Streets' => 'otherServices.cleanUp',
        	'Electricity Appendix Electrical and Code Enforcement' => 'otherServices.electricity',
        );
        
        foreach($simpleChecks as $field => $key){
            $fdf[$field] = $flat[$key]?'/On':'/';
        }
        
        $doubleChecks = array(
            'alcohol' => array(true => array('Yes Alcohol' => '/On'), false => array('No Alcohol' => '/On')),
            'food' => array(true => array('foodbev yes' => '/Yes_3'), false => array('food bev no' => '/No_2')),
            'itemsForSale' => array(true => array('Yes Items for Sale' => '/Yes_2'), false => array('undefined_4' => '/No_3')),
            'event.roadEnclosure' => array(true => array('Yes Closure' => '/Yes'), false => array('No Closure' => '/No')),
        );
        
        foreach($doubleChecks as $key => $config){
            $config = $config[!empty($flat[$key])];
            $fdf[key($config)] = current($config);
        }
        
        $simpleText = array(
            'Event Description' => 'event.purpose',
            'Hours of Operation' => 'event.hours.duringEvent',
        
        	'Name of Organization' => 'organization.name',
        	'Organization Address' => 'organization.address',
        	'Organization Phone' => 'organization.phone',
            'Email_2' => 'organization.email',
            'Fax_2' => 'organization.fax',
        
            'Name of Applicant' => 'applicant.name',
        	'Email' => 'applicant.email',
            'Phone' => 'applicant.phone',
            'Fax' => 'applicant.fax',
        
            'Name of Insurance Carrier' => 'insurance.carrierName',
        	'Address' => 'insurance.address',
            'Phone' => 'insurance.phone',
            'AgentBroker' => 'insurance.agentName',
        
            'Cell Phone' => 'event.contactPerson.phone',
        	'Name' => 'event.contactPerson.name',
        	'Comments' => 'comments',
        	'RAIN DATE' => 'event.eventDate.rain',
        	'Event Dates' => 'event.eventDate.date',
        	'Size of Tents' => 'event.setUp.tents',
            'Event Location Check all that apply' => 'event.location.address',
            
        	'List of Vendors' => 'food',
        	'Describe Items' => 'itemsForSale',
        	'Streets to be closed' => 'event.roadEnclosure',
            'If greater than 5000 see Appendix EMS' => 'event.participants',
        
            'Print Name' => 'applcantName',
            'Date' => 'date',
        
        );
        
        foreach($simpleText as $field => $key){
            $fdf[$field] = '(' . $flat[$key] . ')';
        }
        
        //one off
        $fdf['During event Set upTake Down'] = '(' . $flat['event.hours.setUp'] . ' ' . $flat['event.hours.takeDown'] . ')';
        
        //type radio
        $map = array(
            "block" => "Block Party",
            "parade" => "Parade",
            "festival" => "Festival",
            "walk" => "WalkRun",
        );
        
        foreach($map as $option => $field){
            if($flat['event.type'] == $option){
                $fdf[$field] = '/On';
            } else {
                $fdf[$field] = '/';
            }
        }
        
        if(!in_array($flat['event.type'], $map)){
                $fdf['Event Type Other'] = '(' . $flat['event.type'] . ')';
                $fdf['Other_2'] = '/On';
        }
        
        //org type radio
        $map = array(
            "block" => "NonProfit",
            "parade" => "Profit",
        );
        
        foreach($map as $option => $field){
            if($flat['organization.type'] == $option){
                $fdf[$field] = '/On';
            } else {
                $fdf[$field] = '/';
            }
        }
        
        if(!in_array($flat['organization.type'], $map)){
                $fdf['Other Details'] = '(' . $flat['organization.type'] . ')';
                $fdf['Other'] = '/On';
        }
        
        //fdf template
        ob_start();
        ?>
%FDF-1.2
%âãÏÓ
1 0 obj 
<<
/FDF 
<<
/Fields [
<?php foreach($fdf as $t => $v):?>
<<
/V <?php echo $v?>

/T (<?php echo $t?>)
>> 
<?php endforeach;?>
]
>>
>>
endobj 
trailer

<<
/Root 1 0 R
>>
%%EOF        
        <?php
        return ob_get_clean(); 
    }
    
    function flatten($array, $prefix = '') {
        $result = array();
        foreach($array as $key=>$value) {
            if(is_array($value)) {
                if('address' == $key){
                    $result[$prefix . $key] = $value['street'] . ', ' . $value['state'] . ' ' . $value['zip'];
                } else {
                    $result = $result + $this->flatten($value, $prefix . $key . '.');
                }
            }
            else {
                $result[$prefix . $key] = $value;
            }
        }
        return $result;
    }
}
