<?php
use Sarfraznawaz2005\AiTeam\Contracts\DataProviderInterface;

class JobSearcherDataProvider implements DataProviderInterface
{
    public function get(): string
    {
        // this could come from your database or api for example.
		
        return <<<jobs
		1. **Company:** InVitro Cell Research, LLC
			- **Location:** Leonia, Bergen County
			- **Description:** Hiring Senior Data Scientists with expertise in integrating and analyzing multi-omic datasets.

		2. **Company:** Fingerprint For Success
			- **Location:** Manhattan, New York City
			- **Description:** Inviting professionals in high-growth industries thinking about their next move or looking to transition into the field of data science.

		3. **Company:** Curinos
			- **Location:** New York City, New York
			- **Description:** Looking for an experienced applied Senior Data Scientist to join our Data Science team.

		4. **Company:** Chubb
			- **Location:** Hudson County, New Jersey
			- **Description:** Seeking an experienced Senior Data Scientist for our North America Property and Casualty Data Analytics Division.

		5. **Company:** Informa
			- **Location:** Church Street, Manhattan
			- **Description:** Curinos is looking for an experienced applied Senior Data Scientist to join our Data Science & Machine Learning team.
		jobs;
    }
}
