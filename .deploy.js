const ftpDeploy = require("@samkirkland/ftp-deploy");

const prompt = require('prompt');

const properties = [
	{
		name: 'username',
	},
    {
        name: 'password',
        hidden: true
    }
];

async function deployMyCode( username, password ) {
  console.log("🚚 Deploy started");
  await ftpDeploy.deploy({
    server: "ftp.pcouncil.org",
    username: username,
    password: password,
    exclude: [
		...ftpDeploy.excludeDefaults,
		".github/**",
		".deploy.js",
		".deploy_ignore",
		".gitignore",
		".postcssrc.js",
		".stylelintrc",
		".ftp-deploy-sync-state.json",
		"package.json",
		"package-lock.json"
	],
	'server-dir': '/usr/www/pcouncil/htdocs/wp-content/plugins/pfmc-feature-set/'
  });
  console.log("🚀 Deploy done!");
}

prompt.start();

prompt.get(properties, function (err, result) {
    if (err) { return onErr(err); }
	deployMyCode( result.username, result.password );
});
