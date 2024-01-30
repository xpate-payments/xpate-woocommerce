# Xpate plugin for Wordpress WooCommerce

## About
This is the offical Xpate plugin.

## Version number
Version 1.0.0

## Pre-requisites to install the plug-ins 
* PHP v5.4 and above
* MySQL v5.4 and above

## Installation
Manual installation of the Xpate WooCommerce plugin using (s)FTP

1. Upload the folder 'xpate' in the ZIP file into the 'wp-content/plugins' folder of your WordPress installation.
You can use an sFTP or SCP program, for example, to upload the files. There are various sFTP clients that you can download free of charge from the internet, such as WinSCP or Filezilla.
2. Activate the Xpate plugin in ‘Plugins’ > Installed Plugins.
3. Select ‘WooCommerce’ > ‘Settings’ > Payments and click on Xpate (Enabled).
4. Configure the Xpate module ('Manage' button)
- Copy the API key
- Are you offering Klarna on your pay page? In that case enter the following fields:
	- Test API key field. Copy the API Key of your test webshop in the Test API key field.
	When your Klarna application is approved an extra test webshop was created for you to use in your test with Klarna. The name of this webshop starts with ‘TEST Klarna’.
	- Klarna IP
	For the payment method Klarna you can choose to offer it only to a limited set of whitelisted IP addresses. You can use this for instance when you are in the testing phase and want to make sure that Klarna is not available yet for your customers.
	If you do not offer Klarna you can leave the Test API key and Klarna debug IP fields empty.
- Are you offering Afterpay on your pay page? 
	- To do this click on the “Manage” button of Xpate: AfterPay in the payment method overview.
	- Next, see the instructions for Klarna
- Select your preferred Failed payment page. This setting determines the page to which your customer is redirected after a payment attempt has failed. You can choose between the Checkout page (the page where you can choose a payment method) or the Shopping cart page (the page before checkout where the content of the shopping cart is displayed).
- Enable the cURL CA bundle option.
This fixes a cURL SSL Certificate issue that appears in some web-hosting environments where you do not have access to the PHP.ini file and therefore are not able to update server certificates.
- Only for AfterPay payment: To allow AfterPay to be used for any other country just add its country code (in ISO 2 standard) to the "Countries available for AfterPay" field. Example: BE, NL, FR
- Each payment method has a Allowed currencies(settlement) setting with which it works. Depending on this setting, the selected store currency and the allowed currencies for the Xpate gateway, payment methods will be filtered on the Checkout page. This setting can be edited for each payment method, if some currencies are not added, but the payment method works with it.
5. Configure each payment method you would like to offer in your webshop.
Enable only those payment methods that you applied for and for which you have received a confirmation from us.
- To configure iDEAL do the following:
	- Go to ‘WooCommerce’ > ‘Settings’ > Payments > ‘Xpate: iDEAL’.
	- Select Enable iDEAL Payment to include the payment method in your pay page.
- Follow the same procedure for all other payment methods you have enabled.

Manual installation by uploading ZIP file from WordPress administration environment

1. Go to your WordPress admin environment. Upload the ZIP file to your WordPress installation by clicking on ‘Plugins’ > ‘Add New’. No files are overwritten.
2. Select ´Upload plugin´.
3. Select the xpate.zip file.
4. Continue with step 3 of Installation using (s)FTP.

Compatibility: WordPress 5.6 or higher