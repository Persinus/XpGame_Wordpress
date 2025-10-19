<?php

if (!defined('ABSPATH')) exit;


use MailPoetVendor\Twig\Environment;
use MailPoetVendor\Twig\Error\LoaderError;
use MailPoetVendor\Twig\Error\RuntimeError;
use MailPoetVendor\Twig\Extension\CoreExtension;
use MailPoetVendor\Twig\Extension\SandboxExtension;
use MailPoetVendor\Twig\Markup;
use MailPoetVendor\Twig\Sandbox\SecurityError;
use MailPoetVendor\Twig\Sandbox\SecurityNotAllowedTagError;
use MailPoetVendor\Twig\Sandbox\SecurityNotAllowedFilterError;
use MailPoetVendor\Twig\Sandbox\SecurityNotAllowedFunctionError;
use MailPoetVendor\Twig\Source;
use MailPoetVendor\Twig\Template;

/* layout.html */
class __TwigTemplate_8581a8ede6c645c86df82d14a0f9727da4ff92aa0bb85d1970f9d5219782671e extends Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
            'templates' => [$this, 'block_templates'],
            'container' => [$this, 'block_container'],
            'title' => [$this, 'block_title'],
            'content' => [$this, 'block_content'],
            'after_translations' => [$this, 'block_after_translations'],
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 1
        yield "<!-- pre connect to 3d party to speed up page loading -->
<link rel=\"preconnect\" href=\"http://cdn.mxpnl.com\">
<link rel=\"dns-prefetch\" href=\"http://cdn.mxpnl.com\">

<!-- system notices -->
<div id=\"mailpoet_notice_system\" class=\"mailpoet_notice\" style=\"display:none;\"></div>

<!-- handlebars templates -->
";
        // line 9
        yield from $this->unwrap()->yieldBlock('templates', $context, $blocks);
        // line 10
        yield "
<!-- main container -->
";
        // line 12
        yield from $this->unwrap()->yieldBlock('container', $context, $blocks);
        // line 33
        yield "
<script type=\"text/javascript\">
  var mailpoet_wp_locale = \"";
        // line 35
        yield $this->extensions['MailPoet\Twig\I18n']->getLocale();
        yield "\";
  var mailpoet_datetime_format = \"";
        // line 36
        yield $this->env->getRuntime('MailPoetVendor\Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('MailPoetVendor\Twig\Runtime\EscaperRuntime')->escape($this->extensions['MailPoet\Twig\Functions']->getWPDateTimeFormat(), "js"), "html", null, true);
        yield "\";
  var mailpoet_date_format = \"";
        // line 37
        yield $this->env->getRuntime('MailPoetVendor\Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('MailPoetVendor\Twig\Runtime\EscaperRuntime')->escape($this->extensions['MailPoet\Twig\Functions']->getWPDateFormat(), "js"), "html", null, true);
        yield "\";
  var mailpoet_time_format = \"";
        // line 38
        yield $this->env->getRuntime('MailPoetVendor\Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('MailPoetVendor\Twig\Runtime\EscaperRuntime')->escape($this->extensions['MailPoet\Twig\Functions']->getWPTimeFormat(), "js"), "html", null, true);
        yield "\";
  var mailpoet_date_offset = ";
        // line 39
        yield json_encode(get_option("gmt_offset"));
        yield ";
  var mailpoet_version = \"";
        // line 40
        yield $this->extensions['MailPoet\Twig\Functions']->getMailPoetVersion();
        yield "\";
  var mailpoet_locale = \"";
        // line 41
        yield $this->extensions['MailPoet\Twig\Functions']->getTwoLettersLocale();
        yield "\";
  var mailpoet_wp_week_starts_on = \"";
        // line 42
        yield $this->extensions['MailPoet\Twig\Functions']->getWPStartOfWeek();
        yield "\";
  var mailpoet_urls = ";
        // line 43
        yield json_encode(($context["urls"] ?? null));
        yield ";
  var mailpoet_premium_version = ";
        // line 44
        yield json_encode($this->extensions['MailPoet\Twig\Functions']->getMailPoetPremiumVersion());
        yield ";
  var mailpoet_main_page_slug =   ";
        // line 45
        yield json_encode(($context["main_page"] ?? null));
        yield ";
  var mailpoet_3rd_party_libs_enabled = ";
        // line 46
        yield $this->env->getRuntime('MailPoetVendor\Twig\Runtime\EscaperRuntime')->escape(json_encode($this->extensions['MailPoet\Twig\Functions']->libs3rdPartyEnabled()), "html", null, true);
        yield ";
  var mailpoet_analytics_enabled = ";
        // line 47
        yield $this->env->getRuntime('MailPoetVendor\Twig\Runtime\EscaperRuntime')->escape(json_encode($this->extensions['MailPoet\Twig\Analytics']->isEnabled()), "html", null, true);
        yield ";
  var mailpoet_analytics_public_id = ";
        // line 48
        yield json_encode($this->extensions['MailPoet\Twig\Analytics']->getPublicId());
        yield ";
  var mailpoet_analytics_new_public_id = ";
        // line 49
        yield $this->env->getRuntime('MailPoetVendor\Twig\Runtime\EscaperRuntime')->escape(json_encode($this->extensions['MailPoet\Twig\Analytics']->isPublicIdNew()), "html", null, true);
        yield ";
  var mailpoet_free_domains = ";
        // line 50
        yield json_encode($this->extensions['MailPoet\Twig\Functions']->getFreeDomains());
        yield ";
  var mailpoet_woocommerce_active = ";
        // line 51
        yield json_encode($this->extensions['MailPoet\Twig\Functions']->isWoocommerceActive());
        yield ";
  var mailpoet_woocommerce_email_improvements_enabled = ";
        // line 52
        yield json_encode($this->extensions['MailPoet\Twig\Functions']->isWoocommerceEmailImprovementsEnabled());
        yield ";
  var mailpoet_woocommerce_bookings_active = ";
        // line 53
        yield json_encode(($context["is_woocommerce_bookings_active"] ?? null));
        yield ";
  var mailpoet_woocommerce_subscriptions_active = ";
        // line 54
        yield json_encode(($context["is_woocommerce_subscriptions_active"] ?? null));
        yield ";
  var mailpoet_woocommerce_store_config = ";
        // line 55
        yield json_encode(($context["woocommerce_store_config"] ?? null));
        yield ";
  // RFC 5322 standard; http://emailregex.com/ combined with https://google.github.io/closure-library/api/goog.format.EmailAddress.html#isValid
  var mailpoet_email_regex = /(?=^[+a-zA-Z0-9_.!#\$%&'*\\/=?^`{|}~-]+@([a-zA-Z0-9-]+\\.)+[a-zA-Z0-9]{2,63}\$)(?=^(([^<>()\\[\\]\\\\.,;:\\s@\"]+(\\.[^<>()\\[\\]\\\\.,;:\\s@\"]+)*)|(\".+\"))@((\\[[0-9]{1,3}\\.[0-9]{1,3}\\.[0-9]{1,3}\\.[0-9]{1,3}])|(([a-zA-Z\\-0-9]+\\.)+[a-zA-Z]{2,})))/;
  var mailpoet_feature_flags = ";
        // line 58
        yield json_encode(($context["feature_flags"] ?? null));
        yield ";
  var mailpoet_referral_id = ";
        // line 59
        yield json_encode(($context["referral_id"] ?? null));
        yield ";
  var mailpoet_wp_segment_state = ";
        // line 60
        yield json_encode(($context["wp_segment_state"] ?? null));
        yield ";
  var mailpoet_mta_method = '";
        // line 61
        yield $this->env->getRuntime('MailPoetVendor\Twig\Runtime\EscaperRuntime')->escape(($context["mta_method"] ?? null), "html", null, true);
        yield "';
  var mailpoet_tracking_config = ";
        // line 62
        yield json_encode(($context["tracking_config"] ?? null));
        yield ";
  var mailpoet_is_new_user = ";
        // line 63
        yield json_encode((($context["is_new_user"] ?? null) == true));
        yield ";
  var mailpoet_installed_days_ago = ";
        // line 64
        yield json_encode(($context["installed_days_ago"] ?? null));
        yield ";
  var mailpoet_send_transactional_emails = ";
        // line 65
        yield json_encode(($context["send_transactional_emails"] ?? null));
        yield ";
  var mailpoet_transactional_emails_opt_in_notice_dismissed = ";
        // line 66
        yield json_encode(($context["transactional_emails_opt_in_notice_dismissed"] ?? null));
        yield ";
  var mailpoet_deactivate_subscriber_after_inactive_days = ";
        // line 67
        yield json_encode(($context["deactivate_subscriber_after_inactive_days"] ?? null));
        yield ";
  var mailpoet_woocommerce_version = ";
        // line 68
        yield json_encode($this->extensions['MailPoet\Twig\Functions']->getWooCommerceVersion());
        yield ";
  var mailpoet_track_wizard_loaded_via_woocommerce = ";
        // line 69
        yield json_encode(($context["track_wizard_loaded_via_woocommerce"] ?? null));
        yield ";
  var mailpoet_track_wizard_loaded_via_woocommerce_marketing_dashboard = ";
        // line 70
        yield json_encode(($context["track_wizard_loaded_via_woocommerce_marketing_dashboard"] ?? null));
        yield ";
  var mailpoet_mail_function_enabled = '";
        // line 71
        yield $this->env->getRuntime('MailPoetVendor\Twig\Runtime\EscaperRuntime')->escape(($context["mail_function_enabled"] ?? null), "html", null, true);
        yield "';
  var mailpoet_admin_plugins_url = '";
        // line 72
        yield $this->env->getRuntime('MailPoetVendor\Twig\Runtime\EscaperRuntime')->escape(($context["admin_plugins_url"] ?? null), "html", null, true);
        yield "';
  var mailpoet_is_dotcom = ";
        // line 73
        yield json_encode($this->extensions['MailPoet\Twig\Functions']->isDotcom());
        yield ";
  var mailpoet_cron_trigger_method = ";
        // line 74
        yield json_encode(($context["cron_trigger_method"] ?? null));
        yield ";
  var mailpoet_use_block_email_editor_for_automation_emails = ";
        // line 75
        yield json_encode(($context["use_block_email_editor_for_automation_emails"] ?? null));
        yield ";

  var mailpoet_site_name = '";
        // line 77
        yield $this->env->getRuntime('MailPoetVendor\Twig\Runtime\EscaperRuntime')->escape(($context["site_name"] ?? null), "html", null, true);
        yield "';
  var mailpoet_site_url = \"";
        // line 78
        yield $this->env->getRuntime('MailPoetVendor\Twig\Runtime\EscaperRuntime')->escape(($context["site_url"] ?? null), "html", null, true);
        yield "\";
  var mailpoet_site_address = '";
        // line 79
        yield $this->env->getRuntime('MailPoetVendor\Twig\Runtime\EscaperRuntime')->escape(($context["site_address"] ?? null), "html", null, true);
        yield "';

  // Premium status
  var mailpoet_current_wp_user_email = '";
        // line 82
        yield $this->env->getRuntime('MailPoetVendor\Twig\Runtime\EscaperRuntime')->escape($this->env->getRuntime('MailPoetVendor\Twig\Runtime\EscaperRuntime')->escape(($context["current_wp_user_email"] ?? null), "js"), "html", null, true);
        yield "';
  var mailpoet_premium_link = ";
        // line 83
        yield json_encode(($context["link_premium"] ?? null));
        yield ";
  var mailpoet_premium_plugin_installed = ";
        // line 84
        yield json_encode(($context["premium_plugin_installed"] ?? null));
        yield ";
  var mailpoet_premium_active = ";
        // line 85
        yield json_encode(($context["premium_plugin_active"] ?? null));
        yield ";
  var mailpoet_premium_plugin_download_url = ";
        // line 86
        yield json_encode(($context["premium_plugin_download_url"] ?? null));
        yield ";
  var mailpoet_premium_plugin_activation_url = ";
        // line 87
        yield json_encode(($context["premium_plugin_activation_url"] ?? null));
        yield ";
  var mailpoet_has_valid_api_key = ";
        // line 88
        yield json_encode(($context["has_valid_api_key"] ?? null));
        yield ";
  var mailpoet_has_valid_premium_key = ";
        // line 89
        yield json_encode(($context["has_valid_premium_key"] ?? null));
        yield ";
  var mailpoet_has_premium_support = ";
        // line 90
        yield json_encode(($context["has_premium_support"] ?? null));
        yield ";
  var has_mss_key_specified = ";
        // line 91
        yield json_encode(($context["has_mss_key_specified"] ?? null));
        yield ";
  var mailpoet_mss_key_invalid = ";
        // line 92
        yield json_encode(($context["mss_key_invalid"] ?? null));
        yield ";
  var mailpoet_mss_key_valid = ";
        // line 93
        yield json_encode(($context["mss_key_valid"] ?? null));
        yield ";
  var mailpoet_mss_key_pending_approval = '";
        // line 94
        yield $this->env->getRuntime('MailPoetVendor\Twig\Runtime\EscaperRuntime')->escape(($context["mss_key_pending_approval"] ?? null), "html", null, true);
        yield "';
  var mailpoet_mss_active = ";
        // line 95
        yield json_encode(($context["mss_active"] ?? null));
        yield ";
  var mailpoet_plugin_partial_key = '";
        // line 96
        yield $this->env->getRuntime('MailPoetVendor\Twig\Runtime\EscaperRuntime')->escape(($context["plugin_partial_key"] ?? null), "html", null, true);
        yield "';
  var mailpoet_subscribers_count = ";
        // line 97
        yield $this->env->getRuntime('MailPoetVendor\Twig\Runtime\EscaperRuntime')->escape(($context["subscriber_count"] ?? null), "html", null, true);
        yield ";
  var mailpoet_subscribers_counts_cache_created_at = ";
        // line 98
        yield json_encode(($context["subscribers_counts_cache_created_at"] ?? null));
        yield ";
  var mailpoet_subscribers_limit = ";
        // line 99
        ((($context["subscribers_limit"] ?? null)) ? (yield $this->env->getRuntime('MailPoetVendor\Twig\Runtime\EscaperRuntime')->escape(($context["subscribers_limit"] ?? null), "html", null, true)) : (yield "false"));
        yield ";
  var mailpoet_subscribers_limit_reached = ";
        // line 100
        yield json_encode(($context["subscribers_limit_reached"] ?? null));
        yield ";
  var mailpoet_email_volume_limit = ";
        // line 101
        yield json_encode(($context["email_volume_limit"] ?? null));
        yield ";
  var mailpoet_email_volume_limit_reached = ";
        // line 102
        yield json_encode(($context["email_volume_limit_reached"] ?? null));
        yield ";
  var mailpoet_capabilities = ";
        // line 103
        yield json_encode(($context["capabilities"] ?? null));
        yield ";
  var mailpoet_tier = ";
        // line 104
        yield json_encode(($context["tier"] ?? null));
        yield ";
  var mailpoet_cdn_url = ";
        // line 105
        yield json_encode($this->extensions['MailPoet\Twig\Assets']->generateCdnUrl(""));
        yield ";
  var mailpoet_tags = ";
        // line 106
        yield json_encode(($context["tags"] ?? null));
        yield ";

  ";
        // line 108
        if ( !($context["premium_plugin_active"] ?? null)) {
            // line 109
            yield "    var mailpoet_free_premium_subscribers_limit = ";
            yield $this->env->getRuntime('MailPoetVendor\Twig\Runtime\EscaperRuntime')->escape(($context["free_premium_subscribers_limit"] ?? null), "html", null, true);
            yield ";
  ";
        }
        // line 111
        yield "</script>

";
        // line 113
        yield $this->extensions['MailPoet\Twig\I18n']->localize(["topBarLogoTitle" => $this->extensions['MailPoet\Twig\I18n']->translate("Back to section root"), "topBarUpdates" => $this->extensions['MailPoet\Twig\I18n']->translate("Updates"), "whatsNew" => $this->extensions['MailPoet\Twig\I18n']->translate("What’s new"), "updateMailPoetNotice" => $this->extensions['MailPoet\Twig\I18n']->translate("[link]Update MailPoet[/link] to see the latest changes"), "ajaxFailedErrorMessage" => $this->extensions['MailPoet\Twig\I18n']->translate("An error has happened while performing a request, the server has responded with response code %d"), "ajaxTimeoutErrorMessage" => $this->extensions['MailPoet\Twig\I18n']->translate("An error has happened while performing a request, the server request has timed out after %d seconds"), "dismissNotice" => $this->extensions['MailPoet\Twig\I18n']->translate("Dismiss this notice."), "confirmEdit" => $this->extensions['MailPoet\Twig\I18n']->translate("Sending is in progress. Do you want to pause sending and edit the newsletter?"), "confirmAutomaticNewsletterEdit" => $this->extensions['MailPoet\Twig\I18n']->translate("To edit this email, it needs to be deactivated. You can activate it again after you make the changes."), "subscribersLimitNoticeTitle" => $this->extensions['MailPoet\Twig\I18n']->translate("Action required: Upgrade your plan for more than [subscribersLimit] subscribers!"), "subscribersLimitNoticeTitleUnknownLimit" => $this->extensions['MailPoet\Twig\I18n']->translate("Action required: Upgrade your plan!"), "subscribersLimitReached" => $this->extensions['MailPoet\Twig\I18n']->translate("Congratulations on reaching over [subscribersLimit] subscribers!"), "subscribersLimitReachedUnknownLimit" => $this->extensions['MailPoet\Twig\I18n']->translate("Congratulations, you now have more subscribers than your plan’s limit!"), "freeVersionLimit" => $this->extensions['MailPoet\Twig\I18n']->translate("Our free version is limited to [subscribersLimit] subscribers."), "yourPlanLimit" => $this->extensions['MailPoet\Twig\I18n']->translate("Your plan is limited to [subscribersLimit] subscribers."), "youNeedToUpgrade" => $this->extensions['MailPoet\Twig\I18n']->translate("To continue using MailPoet without interruption, it’s time to upgrade your plan."), "actToSeamlessService" => $this->extensions['MailPoet\Twig\I18n']->translate("Act now to ensure seamless service to your growing audience."), "checkHowToManageSubscribers" => $this->extensions['MailPoet\Twig\I18n']->translate("Alternatively, [link]check how to manage your subscribers[/link] to keep your numbers below your plan’s limit."), "upgradeNow" => $this->extensions['MailPoet\Twig\I18n']->translate("Upgrade Now"), "refreshMySubscribers" => $this->extensions['MailPoet\Twig\I18n']->translate("Refresh subscriber limit"), "emailVolumeLimitNoticeTitle" => $this->extensions['MailPoet\Twig\I18n']->translate("Congratulations, you sent more than [emailVolumeLimit] emails this month!"), "emailVolumeLimitNoticeTitleUnknownLimit" => $this->extensions['MailPoet\Twig\I18n']->translate("Congratulations, you sent a lot of emails this month!"), "youReachedEmailVolumeLimit" => $this->extensions['MailPoet\Twig\I18n']->translate("You have sent more emails this month than your MailPoet plan includes ([emailVolumeLimit]), and sending has been temporarily paused."), "youReachedEmailVolumeLimitUnknownLimit" => $this->extensions['MailPoet\Twig\I18n']->translate("You have sent more emails this month than your MailPoet plan includes, and sending has been temporarily paused."), "toContinueUpgradeYourPlanOrWaitUntil" => $this->extensions['MailPoet\Twig\I18n']->translate("To continue sending with MailPoet Sending Service please [link]upgrade your plan[/link], or wait until sending is automatically resumed on <b>[date]</b>."), "refreshMyEmailVolumeLimit" => $this->extensions['MailPoet\Twig\I18n']->translate("Refresh monthly email limit"), "manageSenderDomainHeaderSubtitle" => $this->extensions['MailPoet\Twig\I18n']->translate("To help your audience and MailPoet authenticate you as the domain owner, please add the following DNS records to your domain’s DNS and click “Verify the DNS records”. Please note that it may take up to 24 hours for DNS changes to propagate after you make the change. [link]Read the guide[/link].", "mailpoet"), "sent" => $this->extensions['MailPoet\Twig\I18n']->translate("Sent"), "notSentYet" => $this->extensions['MailPoet\Twig\I18n']->translate("Not sent yet!"), "renderingProblem" => $this->extensions['MailPoet\Twig\I18n']->translate("There was a problem with rendering!", "mailpoet"), "allSendingPausedHeader" => $this->extensions['MailPoet\Twig\I18n']->translate("All sending is currently paused!"), "allSendingPausedBody" => $this->extensions['MailPoet\Twig\I18n']->translate("Your [link]API key[/link] to send with MailPoet is invalid."), "allSendingPausedPremiumValidBody" => $this->extensions['MailPoet\Twig\I18n']->translate("You are not allowed to use the MailPoet sending service with your current API key. Kindly upgrate to a [link]MailPoet sending plan[/link] or switch your [link]sending method[/link]."), "allSendingPausedLink" => $this->extensions['MailPoet\Twig\I18n']->translate("Purchase a key"), "allSendingPausedPremiumValidLink" => $this->extensions['MailPoet\Twig\I18n']->translate("Upgrade the plan"), "cronPingErrorHeader" => $this->extensions['MailPoet\Twig\I18n']->translate("There is an issue with the MailPoet task scheduler"), "systemStatusConnectionSuccessful" => $this->extensions['MailPoet\Twig\I18n']->translate("Connection successful."), "systemStatusConnectionUnsuccessful" => $this->extensions['MailPoet\Twig\I18n']->translate("Connection unsuccessful."), "systemStatusCronConnectionUnsuccessfulInfo" => $this->extensions['MailPoet\Twig\I18n']->translate("Please consult our [link]knowledge base article[/link] for troubleshooting tips."), "systemStatusIntroCron" => $this->extensions['MailPoet\Twig\I18n']->translate("For the plugin to work, it must be able to establish connection with the task scheduler."), "bridgePingErrorHeader" => $this->extensions['MailPoet\Twig\I18n']->translate("There is an issue with the connection to the MailPoet Sending Service"), "systemStatusMSSConnectionCanNotConnect" => $this->extensions['MailPoet\Twig\I18n']->translate("Currently, your installation can not reach the sending service. If you want to use our service please consult our [link]knowledge base article[/link] for troubleshooting tips."), "close" => $this->extensions['MailPoet\Twig\I18n']->translate("Close"), "today" => $this->extensions['MailPoet\Twig\I18n']->translate("Today")]);
        // line 165
        yield "

";
        // line 167
        yield from $this->unwrap()->yieldBlock('after_translations', $context, $blocks);
        // line 168
        yield "
";
        // line 169
        if ($this->extensions['MailPoet\Twig\Analytics']->isEnabled()) {
            // line 170
            yield "  ";
            yield from             $this->loadTemplate("analytics.html", "layout.html", 170)->unwrap()->yield($context);
        }
        // line 172
        yield "
";
        // line 173
        if ((($context["display_chatbot_widget"] ?? null) &&  !$this->extensions['MailPoet\Twig\Functions']->isDotcomEcommercePlan())) {
            // line 174
            yield "  <script type=\"text/javascript\" src=\"";
            yield $this->extensions['MailPoet\Twig\Assets']->getJavascriptScriptUrl("haw.js");
            yield "\"></script>
  <chat-widget
    id=\"chat\"
    bot=\"mailpoet-chat-support\"
    avatar=\"";
            // line 178
            yield $this->extensions['MailPoet\Twig\Assets']->generateCdnUrl("chat-avatar.png");
            yield "\"
    title=\"";
            // line 179
            yield $this->extensions['MailPoet\Twig\I18n']->translate("MailPoet support", "mailpoet");
            yield "\"
    subtitle=\"";
            // line 180
            yield $this->extensions['MailPoet\Twig\I18n']->translate("Chat with our AI assistant", "mailpoet");
            yield "\"
    first-message=\"";
            // line 181
            yield $this->extensions['MailPoet\Twig\I18n']->translate("How can I help you today?", "mailpoet");
            yield "\"
  >
    ";
            // line 183
            $context["support_form_url"] = ((($context["has_premium_support"] ?? null)) ? ("https://www.mailpoet.com/support/support-form/") : ("https://wordpress.org/support/plugin/mailpoet/"));
            // line 184
            yield "    <a slot=\"support-link\"
        href=\"";
            // line 185
            yield $this->env->getRuntime('MailPoetVendor\Twig\Runtime\EscaperRuntime')->escape(($context["support_form_url"] ?? null), "html", null, true);
            yield "\"
        id=\"mailpoet-support-link\"
        style=\"display:none\"
        target=\"_blank\"
    >
      ";
            // line 190
            yield $this->extensions['MailPoet\Twig\I18n']->translate("Contact support", "mailpoet");
            yield "
    </a>
  </chat-widget>
  <script>
    const getChatId = () => localStorage.getItem('ai-widget-mailpoet-chat-support-chat-id');

    document.addEventListener('DOMContentLoaded', function() {
      const supportLink = document.getElementById('mailpoet-support-link');
      if (supportLink) {
        ";
            // line 199
            if (($context["has_premium_support"] ?? null)) {
                // line 200
                yield "          supportLink.addEventListener('click', function(e) {
            const chatId = getChatId();
            if (chatId) {
              e.preventDefault();
              window.open(`";
                // line 204
                yield $this->env->getRuntime('MailPoetVendor\Twig\Runtime\EscaperRuntime')->escape(($context["support_form_url"] ?? null), "html", null, true);
                yield "?chatId=\${chatId}`, '_blank');
            }
          });
        ";
            }
            // line 208
            yield "
        // Only show the support link once the chat has begun
        const checkChatId = setInterval(function() {
          const chatId = getChatId();
          if (chatId) {
            supportLink.style.display = 'block';
            clearInterval(checkChatId);
          }
        }, 2000);
      }
      // Prevent conflicts with other keydown events on Dotcom
      function writingFixer (e) {
          e.stopPropagation();
      }
      document.getElementById('chat').addEventListener('keydown', writingFixer);
    });
  </script>
";
        }
        // line 226
        yield "
<div id=\"mailpoet-modal\"></div>
";
        return; yield '';
    }

    // line 9
    public function block_templates($context, array $blocks = [])
    {
        $macros = $this->macros;
        return; yield '';
    }

    // line 12
    public function block_container($context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 13
        yield "<div class=\"wrap\">
  <div class=\"wp-header-end\"></div>
  <!-- notices -->
  <div id=\"mailpoet_notice_error\" class=\"mailpoet_notice\" style=\"display:none;\"></div>
  <div id=\"mailpoet_notice_success\" class=\"mailpoet_notice\" style=\"display:none;\"></div>
  <!-- React notices -->
  <div id=\"mailpoet_notices\"></div>

  <!-- Set FROM address modal React root -->
  <div id=\"mailpoet_set_from_address_modal\"></div>

  <!-- Set Authorize sender email React root -->
  <div id=\"mailpoet_authorize_sender_email_modal\"></div>

  <!-- title block -->
  ";
        // line 28
        yield from $this->unwrap()->yieldBlock('title', $context, $blocks);
        // line 29
        yield "  <!-- content block -->
  ";
        // line 30
        yield from $this->unwrap()->yieldBlock('content', $context, $blocks);
        // line 31
        yield "</div>
";
        return; yield '';
    }

    // line 28
    public function block_title($context, array $blocks = [])
    {
        $macros = $this->macros;
        return; yield '';
    }

    // line 30
    public function block_content($context, array $blocks = [])
    {
        $macros = $this->macros;
        return; yield '';
    }

    // line 167
    public function block_after_translations($context, array $blocks = [])
    {
        $macros = $this->macros;
        return; yield '';
    }

    /**
     * @codeCoverageIgnore
     */
    public function getTemplateName()
    {
        return "layout.html";
    }

    /**
     * @codeCoverageIgnore
     */
    public function isTraitable()
    {
        return false;
    }

    /**
     * @codeCoverageIgnore
     */
    public function getDebugInfo()
    {
        return array (  520 => 167,  513 => 30,  506 => 28,  500 => 31,  498 => 30,  495 => 29,  493 => 28,  476 => 13,  472 => 12,  465 => 9,  458 => 226,  438 => 208,  431 => 204,  425 => 200,  423 => 199,  411 => 190,  403 => 185,  400 => 184,  398 => 183,  393 => 181,  389 => 180,  385 => 179,  381 => 178,  373 => 174,  371 => 173,  368 => 172,  364 => 170,  362 => 169,  359 => 168,  357 => 167,  353 => 165,  351 => 113,  347 => 111,  341 => 109,  339 => 108,  334 => 106,  330 => 105,  326 => 104,  322 => 103,  318 => 102,  314 => 101,  310 => 100,  306 => 99,  302 => 98,  298 => 97,  294 => 96,  290 => 95,  286 => 94,  282 => 93,  278 => 92,  274 => 91,  270 => 90,  266 => 89,  262 => 88,  258 => 87,  254 => 86,  250 => 85,  246 => 84,  242 => 83,  238 => 82,  232 => 79,  228 => 78,  224 => 77,  219 => 75,  215 => 74,  211 => 73,  207 => 72,  203 => 71,  199 => 70,  195 => 69,  191 => 68,  187 => 67,  183 => 66,  179 => 65,  175 => 64,  171 => 63,  167 => 62,  163 => 61,  159 => 60,  155 => 59,  151 => 58,  145 => 55,  141 => 54,  137 => 53,  133 => 52,  129 => 51,  125 => 50,  121 => 49,  117 => 48,  113 => 47,  109 => 46,  105 => 45,  101 => 44,  97 => 43,  93 => 42,  89 => 41,  85 => 40,  81 => 39,  77 => 38,  73 => 37,  69 => 36,  65 => 35,  61 => 33,  59 => 12,  55 => 10,  53 => 9,  43 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "layout.html", "/home/circleci/mailpoet/mailpoet/views/layout.html");
    }
}
