<?php
class navigation extends Script
{
	protected $description = 'Navigation-Plugin. Navigate from A to B';
	protected $helpMessage = "'navigation from <START> to <DESTINATION>': Returns a map, containing the route from the start-point to the destination\n
	'nav from <START> to <DESTINATION>'\n'navi from <START> to <DESTINATION>'\n";

	private $mode;

	public function run()
	{
		if($this->matches[1] === $this->matches[2]) {
			return $this->send('So... You wanna drive from ' . $this->matches[1] . ' to exact the same place?');
		} else {
			$url = 'https://maps.googleapis.com/maps/api/directions/json?mode=driving&origin=' . urlencode($this->matches[1]) . '&destination=' . urlencode($this->matches[2]) . '&sensor=false';
			$response = file_get_contents($url);

			$response = json_decode($response);

			$imagePath = 'http://maps.googleapis.com/maps/api/staticmap?size=640x640&path=weight:3%7Ccolor:red%7Cenc:' . $response->routes[0]->overview_polyline->points . '&sensor=false';

			//Generate text-based instructions
			$steps = $response->routes[0]->legs[0]->steps;

			$response = '';
			$i = 1;
			foreach ($steps as $step) {
				$instructions = preg_replace('/<div[^>]+>/', ' - ', $step->html_instructions);
				$instructions = preg_replace('/<[^>]+>/', '', $instructions);

				$response .= $i . '. ' . $instructions . ' (' . $step->distance->text . ')' . "\n";
				$i++;
			}

			$this->send($response);
			return $this->send($imagePath, 'image');
		}
	}
}
