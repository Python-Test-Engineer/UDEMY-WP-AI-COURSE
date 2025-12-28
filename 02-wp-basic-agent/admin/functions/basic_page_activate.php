<?php

function wp_basic_agent_activate() {
   // Set default options if needed
   if (!get_option('openai_api_key')) {
        update_option('openai_api_key', '');
    }
}
