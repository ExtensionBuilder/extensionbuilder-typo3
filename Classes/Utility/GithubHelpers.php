<?php
declare(strict_types = 1);

namespace ExtensionBuilder\ExtensionbuilderTypo3\Utility\Github;

class Helpers
{

// https://docs.github.com/de/rest/repos/repos?apiVersion=2022-11-28

    static function findRepos(
        string $gitOrganizations,
        string $gitToken,
        string $gitRepos,
    ): bool {
        $curl_session = curl_init(); 
        curl_setopt($curl_session, CURLOPT_URL, 'https://api.github.com/orgs/' . $gitOrganizations . '/repos');
        curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl_session, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl_session, CURLOPT_HTTPHEADER, [
                'Accept: application/vnd.github+json',
                'Authorization: Bearer '.$gitToken,
                'X-GitHub-Api-Version: 2022-11-28',
                'User-Agent: Awesome-Octocat-App',
            ]
        );
        $result = curl_exec($curl_session);
        curl_close($curl_session);
        $result = json_decode($result, true);
		foreach ($result ?? [] as $resultKey => $resultData) {
            if (strpos($resultData['name'] ?? '', $gitRepos) === 0) {

// echo "GithubHelpers.php - Git found: ".$gitRepos."<br />";

                return true;
    		}
		}

        return false;
    }


// curl -L \
//   -X POST \
//   -H "Accept: application/vnd.github+json" \
//   -H "Authorization: Bearer <YOUR-TOKEN>"\
//   -H "X-GitHub-Api-Version: 2022-11-28" \
//   https://api.github.com/orgs/ORG/repos \
//   -d '{"name":"Hello-World","description":"This is your first repository","homepage":"https://github.com","private":false,"has_issues":true,"has_projects":true,"has_wiki":true}'




    static function createRepos(
        string $gitOrganizations,
        string $gitToken,
        string $gitRepos,
        string $gitDescription = '',
        string $gitHomepage = '',
        bool $gitPrivate = false,
        bool $gitIssues = false,
        bool $gitProjects = false,
        bool $gitWiki = false,
    ): bool {

        $fields = [
            'name' => $gitRepos,
            'description' => $gitDescription,
            'homepage' => $gitHomepage,
            'private' => $gitPrivate,
            'has_issues' => $gitIssues,
            'has_projects' => $gitProjects,
            'has_wiki' => $gitWiki,
        ];
        // https://api.github.com/orgs/ORG/repos
        $curl_session = curl_init();
        curl_setopt($curl_session, CURLOPT_URL, 'https://api.github.com/orgs/'.$gitOrganizations.'/repos');
        curl_setopt($curl_session, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl_session, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl_session, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl_session, CURLOPT_POSTFIELDS, json_encode($fields));
        curl_setopt($curl_session, CURLOPT_HTTPHEADER, [
                'Accept: application/vnd.github+json',
                'Authorization: Bearer '.$gitToken,
                'X-GitHub-Api-Version: 2022-11-28',
                'User-Agent: Awesome-Octocat-App',
            ]
        );
        $result = curl_exec($curl_session);
        curl_close($curl_session);
        $result = json_decode($result, true);

//debug($result, $gitOrganizations . ' - ' . $gitToken . ' - ' . $gitRepos);
	
//		foreach ($result ?? [] as $resultKey => $resultData) {
//            if (strpos($resultData['name'] ?? '', $gitRepos) == 0 ) {
//                return true;
//    		}
//		}
        return false;
    }

}