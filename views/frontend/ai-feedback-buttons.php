<?php 
$feedbackOptions = $data['labels'] ?? [];
$queryLogId = $data['queryLogId'] ?? 0;

 if($queryLogId > 0 && count($feedbackOptions) > 0) { ?>

<div class="sitka-ai-feedback sitka-ai-feedback-thumbs">
<fieldset id="fieldset-sitka-ai-feedback" aria-atomic="false" aria-relevant="additions text">
    <legend>AI Feedback</legend>
    <ul>
    <?php for ($i=0; $i < count($feedbackOptions); $i++) {  ?>
        <li>
<!-- Positive, Negative, or Neutral sentiment -->
            <?php  if($feedbackOptions[$i]["sentiment_name"] == "Positive") { ?>
            <button class="sitka-btn-ai-feedback sitka-positive-ai-response" data-feedback-label-id="<?= $feedbackOptions[$i]["label_id"] ?>" >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#007040" aria-hidden="true">
                    <path aria-described-by="thumbs-svg-<?= $i ?>" d="M1 21h4V9H1v12zm22-11c0-1.1-0.9-2-2-2h-6.31l0.95-4.57c0.03-0.3-0.07-0.6-0.28-0.82L14.17 2 7.59 8.59C7.22 8.95 7 9.45 7 10v8c0 1.1 0.9 2 2 2h7c0.78 0 1.48-0.45 1.82-1.14L23 12.5c0.12-0.33 0.18-0.68 0.18-1.03v-1.47z"/>
                </svg>
                <?= $feedbackOptions[$i]["label_text"] ?>
            </button>
            <?php } ?>
            <?php  if($feedbackOptions[$i]["sentiment_name"] == "Negative") { ?>
            <button class="sitka-btn-ai-feedback sitka-negative-ai-response" data-feedback-label-id="<?= $feedbackOptions[$i]["label_id"] ?>">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#C30000" aria-hidden="true">
                    <g transform="scale(1,-1) translate(0,-24)">
                        <path d="M1 21h4V9H1v12zm22-11c0-1.1-0.9-2-2-2h-6.31l0.95-4.57c0.03-0.3-0.07-0.6-0.28-0.82L14.17 2 7.59 8.59C7.22 8.95 7 9.45 7 10v8c0 1.1 0.9 2 2 2h7c0.78 0 1.48-0.45 1.82-1.14L23 12.5c0.12-0.33 0.18-0.68 0.18-1.03v-1.47z"/>
                    </g>
                </svg>
                <?= $feedbackOptions[$i]["label_text"] ?>
            </button>
            <?php } ?>
            <?php  if($feedbackOptions[$i]["sentiment_name"] == "Neutral") { ?>
            <button class="sitka-btn-ai-feedback sitka-neutral-ai-response" data-feedback-label-id="<?= $feedbackOptions[$i]["label_id"] ?>" >
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#555555" aria-hidden="true">
                    <g transform="scale(1,-1) translate(0,-24)">
                        <rect x="0" y="8" width="20" height="6" rx="2" />
                    </g>
                </svg>
                <?= $feedbackOptions[$i]["label_text"] ?>
            </button>
            <?php } ?>
        </li>
    <?php } ?>
    </ul>
</fieldset>
</div>
 <?php } ?>

 <script>

    // shows success or failure message.
    function replaceAiFeedbackButtonsWithMessage(submitSucceeded)
    {
        const container = document.querySelector('#fieldset-sitka-ai-feedback');
        const p = document.createElement('p');
        if(submitSucceeded) {
            p.textContent = 'Thank you for your feedback on our AI-Powered Search.';
            var aiFeedbackButtonsList = document.querySelector('#fieldset-sitka-ai-feedback ul');
            aiFeedbackButtonsList.remove();
        }
        else {
            p.textContent = 'Something went wrong. Your feedback could not be sent at this time.';
            document.querySelectorAll('.sitka-btn-ai-feedback').forEach(button => {
                button.disabled = false;
            });
        }
        container.appendChild(p);
    }

    // function to send AI Search Feedback to Sitka
    function sendAiSearchFeedback(feedbackLabelId, searchQuery, aiText, context, queryLogId, feedbackUrl, siteId, feedbackText) {
        // send via ajax - create XML request
        xhr = new XMLHttpRequest();
        xhr.open('POST', feedbackUrl);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        xhr.onload = function() {
            if (xhr.status === 200) {
                // remove the buttons and set a message.
                replaceAiFeedbackButtonsWithMessage(true);
            }
            else if (xhr.status !== 200) 
            {
                replaceAiFeedbackButtonsWithMessage(false);
                console.log('Something went wrong, unable to send feedback via AJAX request.');
            }
        };
        var url = encodeURI(
            'feedbackLabelId=' + feedbackLabelId 
            + '&searchQuery='+searchQuery    
            + '&siteId='+siteId
            + '&aiText='+aiText
            + '&feedbackText='+feedbackText
            + '&queryLogId='+queryLogId
            + '&context='+context
            + '&referrer=' + encodeURI(window.location.pathname) + encodeURIComponent(window.location.search) );
        xhr.send(url);
    }

    // add button listener
    document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.sitka-btn-ai-feedback').forEach(button => {
        button.addEventListener('click', (event) => {
            button.disabled = true;
            var ai_feedbackText = ""; // placeholder for feedback text future implementation
            sendAiSearchFeedback(button.dataset.feedbackLabelId, sitka_searchQuery, sitka_aiText, sitka_ai_links_context, sitka_queryLogId, sitka_ai_feedback_dashboard_url, sitka_siteId, ai_feedbackText);
        });
    });
});
 </script>