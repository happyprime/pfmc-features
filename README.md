# The Pacific Fishery Management Council feature set

## Production release process

Releases are manually uploaded to the pcouncil.org website through the WordPress dashboard.

1. Ensure the [`wp dist archive` command](https://developer.wordpress.org/cli/commands/dist-archive/) is installed.
2. Within the plugin directory, run `wp dist-archive ./ pfmc-feature-set.zip`.
3. Navigate to the pcouncil.org dashboard -> **Plugins** -> **Add New** -> **Upload Plugin**.
4. Select the zip file created in step 2 and click **Install Now**.
5. Verify information and click **Replace current with uploaded**.
