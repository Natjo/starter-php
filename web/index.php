<?php common('header-nav'); ?>

<main id="main">

    <?php
    hero("homepage", [
        "suptitle" => "Internal <strong>Ai hub</strong>",
        "title" => "Ai at<br>the service<br>of creativity",
        "text" => "<p>Richard is  your internal hub for AI-powered creative excellence.<br>Discover tolls, best practices, and resources to elevate eve@ry project.@</p>",
        "items" => [
            [
                "images" => THEME_UPLOADS . "hero-1.jpg"
            ],
            [
                "images" => THEME_UPLOADS . "hero-2.jpg"
            ],
            [
                "images" => THEME_UPLOADS . "hero-3.jpg"
            ],
        ]

    ]);
    ?>

    <?php
    strate("our_foundations",  [
        "options" => [
            "id" => "our_foundations",
        ],
        "suptitle" => "Our foundation : the hybrid AI <sup>TM</sup> charter",
        "title" => "Creative value with AI safely, creatively, competively.<br>Six principles that guide how Lonsdale uses AI to outpace creativity, bot replace it.",
        "items" => [
            [
                "title" => "Human authority",
                "text" => "<p>AI augments human intelligence, it never replaces it. We don't create — we curate, direct and validate. Every creative, strategic and final decision is signed off by human experts.</p>",
                "image" => THEME_UPLOADS . "foundation-1.jpg"
            ],
            [
                "title" => "Brand intelligence system",
                "text" => "<p>Your brand becomes a system, not just aguideline. We build AI systems trained on your brand's codes, not generic datasets — crafted to be distinctive, relevant and lasting.</p>",
                "image" => THEME_UPLOADS . "foundation-2.jpg"
            ],
            [
                "title" => "Creative super-exploration",
                "text" => "<p>From 10 ideas to 100+ territories explored. We don't generate ideas — we explore entire creative universes. It replaces neither vision, nor art direction, nor judgment.</p>",
                "image" => THEME_UPLOADS . "foundation-1.jpg"
            ],
            [
                "title" => "Radical transparency",
                "text" => "<p>You always know what is human, what is enhanced, and why. Each deliverable includes a clear AI usage disclosure within a controlled ethical and regulatory framework.</p>",
                "image" => THEME_UPLOADS . "foundation-2.jpg"
            ],
            [
                "title" => "No-risk AI infrastructure",
                "text" => "<p>No data leakage. No shared learning. No compromise. Aligned with internal governance and global AI standards, your data stays protected at every step.</p>",
                "image" => THEME_UPLOADS . "foundation-1.jpg"
            ],
            [
                "title" => "Value multiplication",
                "text" => "<p>AI reduces production. Humans multiply impact. This approach lets us go faster and further — enabling scale, speed and amplified impact without compromising standards.</p>",
                "image" => THEME_UPLOADS . "foundation-2.jpg"
            ]
        ]
    ]);
    ?>

    <?php
    strate("platform",  [
        "options" => [
            "id" => "platform",
        ],
        "suptitle" => "Platform",
        "title" => "Dust",
        "text" => "<p>Dust is our central AI platform for building and deploying intelligent assistants. Access custom agents, connect your data sources, and supercharge your creative process.</p>",
        "link" => [
            "title" => "Access dust",
            "url" => "#",
            "target" => "_blank",
        ],

        "items" => [
            [
                "icon" => "bdd",
                "title" => "Conversational AI assistants tailored to your workflows",
                "text" => "<p>Browse available assistants or create your own to match the way your teams already work.</p>",
            ],
            [
                "icon" => "ai",
                "title" => "Connect your knowledge base for contextual answers",
                "text" => "<p>Connect relevant data sources (Drive, Notion, Slack) so assistants answer with your real context.</p>",
            ],
            [
                "icon" => "chat",
                "title" => "Build custom AI agents without code",
                "text" => "<p>Start conversing and building workflows in minutes, no engineering resources required.</p>",
            ],
        ],
        "platforms" => [
            [
                "title" => "Log in at <strong>dust.tt</strong> with your agency credentials",
                "image" => THEME_UPLOADS . "platform.jpg"
            ],
            [
                "title" => "Browse available assistants or create your own",
                "image" => THEME_UPLOADS . "platform.jpg"
            ],
            [
                "title" => "Connect relevant data sources (Drive, Notion, Slack)",
                "image" => THEME_UPLOADS . "platform.jpg"
            ],
            [
                "title" => "Start conversing and building workflows",
                "image" => THEME_UPLOADS . "platform.jpg"
            ]
        ]
    ]);
    ?>
    <?php
    strate("separator");
    ?>
    <?php
    strate("toolkit",  [
        "options" => [
            "id" => "toolkit",
        ],
        "suptitle" => "<strong>Toolkit</strong> : creative solutions",
        "title" => "Our curated catalog of AI tools approved for agency use. Each solution has been vetted for quality, security, and creative value.",
        "items" => [
            [
                "suptitle" => "Image generation",
                "icon" => THEME_UPLOADS . "midjourney.svg",
                "title" => "Midjourney",
                "text" => "<p>Create high-fidelity visuals, mood boards, and concept art. Use detailed prompts with style references for brand-consistent results.</p>",
                "usage" => "<p>Access via Discord. Start prompts with /imagine. Use --ar for aspect ratios, --style for aesthetics.</p>"
            ],
            [
                "suptitle" => "Text & strategy",
                "icon" => "chatgpt",
                "title" => "ChatGPT",
                "text" => "<p>Create high-fidelity visuals, mood boards, and concept art. Use detailed prompts with style references for brand-consistent results.</p>",
                "usage" => "<p>Access via Discord. Start prompts with /imagine. Use --ar for aspect ratios, --style for aesthetics.</p>"

            ],
            [
                "suptitle" => "Image generation",
                "icon" => "chatgpt",
                "title" => "DALL·E 3",
                "text" => "<p>Create high-fidelity visuals, mood boards, and concept art. Use detailed prompts with style references for brand-consistent results.</p>",
                "usage" => "<p>Access via Discord. Start prompts with /imagine. Use --ar for aspect ratios, --style for aesthetics.</p>"

            ]
        ]
    ]);
    ?>

    <?php
    strate("showcase",  [
        "options" => [
            "id" => "showcase",
        ],
        "suptitle" => "Showcase",
        "title" => "Made by human powered <strong>by AI</strong>.",
        "items" => [
            [
                [
                    "image" => THEME_UPLOADS . "showcase-2.jpg",
                ],
                [
                    "image" => THEME_UPLOADS . "showcase-5.jpg",
                ],
                [
                    "image" => THEME_UPLOADS . "showcase-3.jpg",
                ],

            ],
            [
                [
                    "image" => THEME_UPLOADS . "showcase-1.jpg",
                ],
                [
                    "image" => THEME_UPLOADS . "showcase-4.jpg",
                ],
            ],
            [
                [
                    "image" => THEME_UPLOADS . "showcase-4.jpg",
                ],
                [
                    "image" => THEME_UPLOADS . "showcase-6.jpg",
                ],
            ],

            [
                [
                    "isVideo" => true,
                    "video" => THEME_UPLOADS . "showcase-1.mp4"
                ],
                [
                    "image" => THEME_UPLOADS . "showcase-2.jpg",
                ],
                [
                    "image" => THEME_UPLOADS . "showcase-5.jpg",
                ],
            ]

        ]
    ]);
    ?>


    <?php
    strate("learn",  [
        "options" => [
            "id" => "learn",
        ],
        "suptitle" => "Learn",
        "title" => "Best practices & training",
        "items" => [
            [
                "suptitle" => "Training",
                "title" => "AI FUNDAMENTALS WORKSHOP",
                "icon" =>  "video",
                "text" => "<p>A 2-hour interactive session covering prompt engineering, tool selection, and ethical AI use for creative teams.</p>",
                "link" => [
                    "title" => "",
                    "url" => "/",
                    "target" => "_blank",
                ]
            ],
            [
                "suptitle" => "Training",
                "title" => "AI FUNDAMENTALS WORKSHOP",
                "icon" =>  "training",
                "text" => "<p>A 2-hour interactive session covering prompt engineering, tool selection, and ethical AI use for creative teams.</p>",
                "link" => [
                    "title" => "",
                    "url" => "",
                    "target" => "_blank",
                ]
            ],
            [
                "suptitle" => "Training",
                "title" => "AI FUNDAMENTALS WORKSHOP",
                "text" => "<p>A 2-hour interactive session covering prompt engineering, tool selection, and ethical AI use for creative teams.</p>",
                "link" => [
                    "title" => "",
                    "url" => "",
                    "target" => "_blank",
                ]
            ],
            [
                "suptitle" => "Training",
                "title" => "AI FUNDAMENTALS WORKSHOP",
                "text" => "<p>A 2-hour interactive session covering prompt engineering, tool selection, and ethical AI use for creative teams.</p>",
                "link" => [
                    "title" => "",
                    "url" => "",
                    "target" => "_blank",
                ]
            ],


        ]
    ]);
    ?>

    <?php
    strate("ai_news",  [
        "options" => [
            "id" => "ai_news",
        ],
        "suptitle" => "AI news",
        "title" => "Stay informed",
        "items" => [
            [
                "source" => "New Verge",
                "date" => "Mars 2026",
                "title" => "GPT-5 brings multimodal reasoning to creative workflows",
                "text" => "<p>OpenAI's latest model can now reason across text, images, and audio simultaneously<br>—opening new possibilities for integrated creative briefs.</p>",
                "link" => [
                    "title" => "",
                    "url" => "/",
                    "target" => "_blank",
                ]
            ],

            [
                "source" => "New Verge",
                "date" => "Mars 2026",
                "title" => "GPT-5 brings multimodal reasoning to creative workflows",
                "text" => "<p>OpenAI's latest model can now reason across text, images, and audio simultaneously<br>—opening new possibilities for integrated creative briefs.</p>",
                "link" => [
                    "title" => "",
                    "url" => "",
                    "target" => "_blank",
                ]
            ],
            [
                "source" => "New Verge",
                "date" => "Mars 2026",
                "title" => "GPT-5 brings multimodal reasoning to creative workflows",
                "text" => "<p>OpenAI's latest model can now reason across text, images, and audio simultaneously<br>—opening new possibilities for integrated creative briefs.</p>",
                "link" => [
                    "title" => "",
                    "url" => "",
                    "target" => "_blank",
                ]
            ],
            [
                "source" => "New Verge",
                "date" => "Mars 2026",
                "title" => "GPT-5 brings multimodal reasoning to creative workflows",
                "text" => "<p>OpenAI's latest model can now reason across text, images, and audio simultaneously<br>—opening new possibilities for integrated creative briefs.</p>",
                "link" => [
                    "title" => "",
                    "url" => "/",
                    "target" => "_blank",
                ]
            ]
        ]
    ]);
    ?>

    <?php
    strate("key_people",  [
        "options" => [
            "id" => "key_people",
        ],
        "suptitle" => "Key people",
        "title" => "Your go-to experts for <strong>AI questions</strong>, guidance, and collaboration across the agency.",
        "placeholder" => THEME_UPLOADS . "people-0.jpg",
        "items" => [
            [
                "name" => "Sophie Marchand",
                "function" => "Senior Prompt Engineer",
                "from" => "AI Strategy & Governance",
                "shares" => [
                    "linkedin" => "/",
                    "email" => "test@test.com"
                ],
                "image" => THEME_UPLOADS . "people-1.jpg"
            ],
            [
                "name" => "Thomas Durand",
                "function" => "Senior Prompt Engineer",
                "from" => "AI Strategy & Governance",
                "shares" => [
                    "linkedin" => "/",
                    "email" => "test@test.com"
                ],
                "image" => THEME_UPLOADS . "people-2.jpg"
            ],
            [
                "name" => "Léa fontaine",
                "function" => "Senior Prompt Engineer",
                "from" => "AI Strategy & Governance",
                "shares" => [
                    "linkedin" => "/",
                    "email" => "test@test.com"
                ],
                "image" => THEME_UPLOADS . "people-3.png"
            ],
            [
                "name" => "Marc Lefèvre",
                "function" => "Senior Prompt Engineer",
                "from" => "AI Strategy & Governance",
                "shares" => [
                    "linkedin" => "/",
                    "email" => "test@test.com"
                ],
                "image" => THEME_UPLOADS . "people-4.png"
            ],
            [
                "name" => "Camille Bernard",
                "function" => "Senior Prompt Engineer",
                "from" => "AI Strategy & Governance",
                "shares" => ["linkedin", "email"],
                "image" => THEME_UPLOADS . "people-5.png"
            ],
            [
                "name" => "Antoine Morel",
                "function" => "Senior Prompt Engineer",
                "from" => "AI Strategy & Governance",
                "shares" => [
                    "linkedin" => "/",
                    "email" => "test@test.com"
                ],
                "image" => THEME_UPLOADS . "people-6.jpg"
            ]
        ],


    ]);
    ?>

    <?php
    strate("hybrid_ai",  [
        "options" => [
            "id" => "hybrid_ai",
        ],
        "text" => "Where<br>human<br>intelligence<br>meets ai power.",
        "subtitle" => "By lonsdale.",
        "images" => [
            "desktop" => THEME_UPLOADS . "image4.jpg",
        ]
    ]);
    ?>

</main>

<?php common('footer'); ?>