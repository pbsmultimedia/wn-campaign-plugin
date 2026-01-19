# Leads

When the recipient clicks a link, a cookie is set with the campaign ID, subscriber ID and link ID. 

This happens at the Links.php controller.

When a lead is created, it should trigger the pbs.campaign.lead event. This must be handled outside the plugin.

The payload is an array with the campaign ID, subscriber ID and link ID.

After the lead is registered, the cookie is deleted.

## Triggering the Event

To trigger the event, use the following code:

```php
Event::fire('pbs.campaign.lead');
```

Cookie wiil be read on the handler.