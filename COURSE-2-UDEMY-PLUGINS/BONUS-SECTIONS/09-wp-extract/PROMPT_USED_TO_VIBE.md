# GOAL

To create a WordPress plugin that given a user query determines which of the WP categories and tags would contain relevant content.

Uses OpenAI chat completions to agentically solve this requirement

## EXAMPLE

A user asks "Tell me about kitchen utensils you stock, particularly cordless ones"

The plugin would get all the cagetgories and tags available, determine which of these are sutiable to answer the query and return for example the following JSON:
{
  "categories": [
    "kitchen appliances",
    "cordless tools"
  ],
  "tags": [
    "knives",
    "blenders"
  ],
  "post_id":[33,55,65]
}

where the "post_id" key contains all the post_ids from the custom table wp_rag_posts defined as:

 id bigint(20) NOT NULL AUTO_INCREMENT,
            guid varchar(255) NOT NULL,
            post_id bigint(20) NOT NULL,
            post_title text NOT NULL,
            post_content longtext NOT NULL,
            categories text,
            tags text,
            custom_meta_data longtext,
            embedding longtext,
            last_updated datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            last_embedded datetime DEFAULT NULL,
            PRIMARY KEY  (id),
            UNIQUE KEY guid (guid),
            KEY post_id (post_id)

**ensure you use $wpdb->prefix.'rag_posts' rather than hard code wp_rag_posts**

Then add a key to this returned JSON of:

"context": "content..." 

where the value is the concatenated text of all the post_title and post_content of all the rows in $wp->prefix.'rag_posts' where the post_id matches the ids in the "ids" key/value in the JSON object returned in the first instance.

## PLUGIN

Admin menu item level 4
"✅ 10 UDEMY  UDEMY EXTRACT"

## STYLE

Keep simple with detailed comments

Take the time you need to get an accurate plugin.


"Title: Wireless Bluetooth Headphones\n\nContent: Premium over-ear headphones with active noise cancellation and 30-hour battery life. Soft memory foam ear cushions provide all-day comfort during extended listening sessions. Foldable design includes protective hard travel case for portability. Built-in microphone enables clear phone calls and voice assistant access. Compatible with all Bluetooth-enabled devices including smartphones and laptops. Quick charge feature provides 5 hours of playback from just 10-minute charge. Available in multiple colors including black, silver, and blue finishes.\n\n---\n\nTitle: Smart Fitness Watch\n\nContent: Track your health metrics with continuous heart rate monitoring, sleep quality tracking, and accurate step counting. Water-resistant up to 50 meters making it perfect for swimming and water sports. Built-in GPS tracking records running and cycling routes without phone. Receives smartphone notifications including calls, texts, and app alerts. Rechargeable battery lasts up to 7 days on single charge. Interchangeable bands available in multiple colors and materials. Syncs seamlessly with both iOS and Android companion apps.\n\n---\n\nTitle: Portable Power Bank 20000mAh\n\nContent: High-capacity portable charger with dual USB ports and USB-C output for versatile charging. Fast charging technology powers your devices quickly and efficiently. LED display shows remaining battery percentage at a glance. Charges iPhone 12 up to 4 times on single charge. Built-in safety features prevent overcharging and overheating. Compact size fits easily in backpacks and purses. Perfect companion for travel, camping, and emergency situations.\n\n---\n\nTitle: 4K Streaming Media Player\n\nContent: Stream your favorite content in stunning 4K Ultra HD resolution with HDR support. Access thousands of channels including Netflix, Hulu, Amazon Prime, and Disney Plus. Voice remote with built-in Alexa for hands-free control. Private listening mode through mobile app or headphone jack. Compact design hides behind TV for clean setup. Easy installation takes just minutes with included HDMI cable. Regular software updates add new features and channels automatically.\n\n---\n\nTitle: Mechanical Gaming Keyboard RGB\n\nContent: Professional-grade mechanical keyboard with customizable RGB backlighting and tactile switches. Each key rated for 50 million keystrokes ensuring years of reliable use. Programmable macro keys enhance gaming performance and productivity. Detachable USB-C cable makes transportation easy. Aluminum frame provides durability and premium feel. Anti-ghosting technology ensures accurate input during intense gaming. Compatible with Windows, Mac, and Linux operating systems.\n\n---\n\nTitle: Wireless Charging Pad 15W\n\nContent: Fast wireless charging pad delivers up to 15W output for compatible devices. Non-slip surface keeps phone securely in place while charging. LED indicator shows charging status without being distracting at night. Works with phone cases up to 5mm thick. Foreign object detection ensures safe charging. Includes USB-C cable and wall adapter. Compatible with iPhone 12 and newer, Samsung Galaxy, and other Qi-enabled devices.\n\n---\n\nTitle: USB-C Hub 7-in-1 Adapter\n\nContent: Expand your laptop connectivity with seven essential ports in compact aluminum design. Includes HDMI port supporting 4K displays at 60Hz. Three USB 3.0 ports for high-speed data transfer. SD and microSD card readers access photos and files. USB-C power delivery port charges laptop while using hub. Plug-and-play design requires no drivers or software. Perfect for MacBook Pro, Dell XPS, and other USB-C laptops.\n\n---\n\nTitle: Smart LED Light Bulbs 4-Pack\n\nContent: Control lighting from anywhere using smartphone app or voice commands. 16 million colors and adjustable brightness create perfect ambiance. Schedule lights to turn on and off automatically. Energy-efficient LED technology lasts 25,000 hours. Works with Alexa, Google Assistant, and Apple HomeKit. No hub required - connects directly to WiFi. Standard E26 base fits most lamps and fixtures.\n\n---\n\nTitle: Webcam 1080p HD with Microphone\n\nContent: Crystal-clear 1080p video calling with auto-focus and light correction technology. Built-in dual microphones capture clear audio from up to 8 feet away. Wide 90-degree field of view fits multiple people in frame. Flexible clip mount fits monitors, laptops, and tripods. Plug-and-play USB connection works instantly without drivers. Privacy shutter protects when camera not in use. Compatible with Zoom, Skype, Microsoft Teams, and other platforms.\n\n---\n\nTitle: Bluetooth Speaker Waterproof\n\nContent: Portable speaker delivers 360-degree sound with deep bass and crisp highs. IPX7 waterproof rating allows submersion up to 3 feet for 30 minutes. 12-hour battery life keeps music playing all day long. Wireless stereo pairing connects two speakers for immersive sound. Built-in microphone enables hands-free calling. Durable rubberized exterior withstands drops and bumps. Includes carabiner clip for attaching to backpacks.\n\n---\n\nTitle: Stainless Steel Cookware Set 10-Piece\n\nContent: Professional-quality cookware set includes essential pots and pans for complete kitchen. Triple-layer base ensures even heat distribution preventing hot spots. Stainless steel construction is oven-safe up to 500 degrees. Tempered glass lids allow monitoring without releasing heat. Riveted handles stay cool during cooking. Dishwasher-safe for easy cleanup. Compatible with all cooktops including induction. Lifetime warranty covers defects.\n\n---\n\nTitle: Air Fryer 6-Quart Digital\n\nContent: Healthy cooking with up to 85% less oil than traditional frying methods. Large 6-quart capacity feeds family of four to six people. Seven preset programs for fries, chicken, steak, fish, and more. Digital touchscreen controls with adjustable temperature up to 400°F. Non-stick basket removes easily for cleaning in dishwasher. Auto shut-off and overheat protection ensure safe operation. Includes recipe book with 50 delicious ideas.\n\n---\n\nTitle: Coffee Maker Programmable 12-Cup\n\nContent: Wake up to fresh coffee with 24-hour programmable timer and auto-brew feature. Brews up to 12 cups in approximately 10 minutes. Pause-and-serve function allows pouring mid-brew without dripping. Permanent gold-tone filter eliminates need for paper filters. Water window shows exact amount before brewing. Keep-warm plate maintains optimal temperature for 2 hours. Easy-view water window and removable filter basket simplify filling and cleaning.\n\n---\n\nTitle: Vacuum Cleaner Cordless Stick\n\nContent: Powerful cordless cleaning with up to 40 minutes of fade-free runtime. Converts to handheld vacuum for furniture, stairs, and car interior. LED headlights illuminate hidden dust and debris. HEPA filtration captures 99.97% of allergens and particles. Washable filter and dustbin reduce ongoing costs. Wall-mount charging dock keeps vacuum ready and organized. Lightweight design reduces arm fatigue during extended cleaning.\n\n---\n\nTitle: Memory Foam Pillow Set of 2\n\nContent: Premium shredded memory foam adjusts to your preferred sleeping position and height. Breathable bamboo cover remains cool throughout the night. Hypoallergenic materials resist dust mites, mold, and allergens. Machine-washable cover removes easily with zipper. Maintains shape and support for years without flattening. CertiPUR-US certified foam meets safety and environmental standards. Suitable for side, back, and stomach sleepers.\n\n---\n\nTitle: Knife Set with Block 15-Piece\n\nContent: German stainless steel blades maintain sharp edge with minimal maintenance. Set includes chef knife, bread knife, utility knife, paring knife, and steak knives. Ergonomic handles provide comfortable grip and control. Wooden block stores knives safely and looks elegant on counter. Full tang construction ensures balance and durability. Hand-wash recommended to preserve blade sharpness. Professional quality at home kitchen price point.\n\n---\n\nTitle: Slow Cooker 6-Quart Programmable\n\nContent: Set-it-and-forget-it cooking with programmable timer up to 20 hours. Large 6-quart capacity perfect for family meals and meal prep. Three heat settings provide cooking flexibility for different recipes. Automatic keep-warm mode activates after cooking completes. Removable ceramic pot is dishwasher-safe for easy cleanup. Glass lid allows monitoring without releasing heat and moisture. Ideal for soups, stews, roasts, and one-pot meals.\n\n---\n\nTitle: Blender High-Speed 1400W\n\nContent: Professional-grade motor blends toughest ingredients including ice and frozen fruit. Variable speed control and pulse function provide precise blending. 64-ounce BPA-free pitcher makes large batches for whole family. Self-cleaning feature simplifies cleanup with water and drop of soap. Hardened stainless steel blades stay sharp through years of use. Includes recipe book with smoothies, soups, and sauces. Ten-year warranty covers motor and parts.\n\n---\n\nTitle: Non-Stick Bakeware Set 5-Piece\n\nContent: Complete baking set includes cookie sheet, cake pans, and muffin tin. Premium non-stick coating ensures easy food release and quick cleanup. Heavy-duty steel construction prevents warping at high temperatures. Oven-safe up to 450 degrees Fahrenheit. Reinforced rolled edges provide strength and durability. Gray color hides scratches better than darker finishes. Dishwasher-safe though hand-washing extends coating life.\n\n---\n\nTitle: Digital Kitchen Scale 11lb Capacity\n\nContent: Precise measurements up to 11 pounds with 0.1-ounce increments for accuracy. Measures in grams, ounces, pounds, and milliliters for recipe flexibility. Tare function zeros out container weight for ingredient-only measurements. Large backlit LCD display shows readings clearly in any lighting. Slim profile stores easily in drawers or cabinets. Auto-off feature preserves battery life. Stainless steel platform wipes clean easily.\n\n---\n"
}