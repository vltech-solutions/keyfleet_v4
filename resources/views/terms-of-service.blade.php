<x-layouts.guest>
    @php
        /*
         * Update manually whenever these Terms materially change.
         */
        $lastUpdated = 'October 3, 2026';
        $version = '3.0';
    @endphp

    <section class="bg-white px-6 py-20 transition-colors duration-300 dark:bg-gray-900">
        <div class="mx-auto max-w-7xl">
            {{-- Header --}}
            <div class="mb-12">
                <div class="flex flex-wrap items-center gap-3">
                    <span
                        class="inline-flex rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700 dark:bg-blue-900/30 dark:text-blue-300"
                    >
                        Legal
                    </span>

                    <span class="text-xs text-gray-400 dark:text-gray-500">
                        Version {{ $version }}
                    </span>
                </div>

                <h1 class="mt-4 text-4xl font-extrabold text-gray-900 dark:text-white">
                    Terms of Service
                </h1>

                <p class="mt-2 text-gray-500 dark:text-gray-400">
                    Last Updated: {{ $lastUpdated }}
                </p>
            </div>

            {{-- Summary --}}
            <div
                class="mb-10 rounded-2xl border border-blue-200 bg-blue-50 p-5 text-sm text-blue-800 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-200"
            >
                <strong class="block text-base">
                    Summary
                </strong>

                <p class="mb-0 mt-2">
                    KeyFleet provides software that helps rental businesses manage their
                    operations. Subscribers remain responsible for their business operations,
                    rental decisions, customer relationships, data collection, users,
                    permissions, and compliance obligations. KeyFleet processes subscriber
                    and renter information only as permitted by these Terms, our Privacy
                    Policy, applicable agreements, and law.
                </p>
            </div>

            <article class="prose max-w-none text-gray-700 dark:text-gray-300 dark:prose-invert">
                {{-- 1 --}}
                <h2 class="dark:text-white">
                    1. Acceptance of Terms
                </h2>

                <p>
                    By creating an account, accessing, purchasing, subscribing to, or using
                    KeyFleet's platform, websites, portals, applications, and related
                    services (collectively, the <strong>"Service"</strong>), you agree to
                    these Terms of Service ("Terms").
                </p>

                <p>
                    These Terms incorporate our
                    <a
                        href="{{ route('privacy-policy') }}"
                        class="text-blue-600 hover:underline dark:text-blue-400"
                    >
                        Privacy Policy
                    </a>
                    and any other terms or agreements expressly incorporated by reference.
                </p>

                <p>
                    If you use the Service on behalf of a company, partnership, organization,
                    or other entity, you represent that you have authority to bind that
                    organization to these Terms.
                </p>

                <p>
                    If you do not agree to these Terms, you must not use the Service.
                </p>

                {{-- 2 --}}
                <h2 class="dark:text-white">
                    2. KeyFleet's Role
                </h2>

                <p>
                    KeyFleet provides software tools for managing rental-related operations.
                    KeyFleet is not the car rental operator in transactions conducted by a
                    subscriber unless expressly stated otherwise.
                </p>

                <p>
                    Subscribers remain responsible for their own:
                </p>

                <ul>
                    <li>rental contracts;</li>
                    <li>vehicles;</li>
                    <li>rates and pricing;</li>
                    <li>booking approvals and cancellations;</li>
                    <li>driver eligibility decisions;</li>
                    <li>security deposits;</li>
                    <li>customer verification;</li>
                    <li>insurance requirements;</li>
                    <li>refund decisions;</li>
                    <li>collection activities;</li>
                    <li>compliance with rental-related laws and regulations;</li>
                    <li>customer disputes and liabilities arising from rental transactions.</li>
                </ul>

                <p>
                    KeyFleet's role is generally limited to providing the technology used
                    by subscribers to manage these activities.
                </p>

                {{-- 3 --}}
                <h2 class="dark:text-white">
                    3. Eligibility and Account Registration
                </h2>

                <h3 class="dark:text-gray-200">
                    3.1 Eligibility
                </h3>

                <p>
                    You must have the legal capacity to enter into these Terms and, where
                    applicable, authority to act on behalf of the subscribing business.
                </p>

                <h3 class="dark:text-gray-200">
                    3.2 Accurate Information
                </h3>

                <p>
                    You agree to provide accurate, current, and complete registration and
                    business information and to update that information when reasonably
                    necessary.
                </p>

                <h3 class="dark:text-gray-200">
                    3.3 Account Security
                </h3>

                <p>
                    You are responsible for protecting your account credentials and controlling
                    access to your account.
                </p>

                <p>
                    You agree to:
                </p>

                <ul>
                    <li>use strong and unique passwords;</li>
                    <li>not knowingly share credentials with unauthorized persons;</li>
                    <li>properly manage user roles and permissions;</li>
                    <li>remove access when personnel no longer require it;</li>
                    <li>notify KeyFleet promptly of suspected unauthorized access;</li>
                    <li>take reasonable precautions to protect devices used to access KeyFleet.</li>
                </ul>

                <p>
                    KeyFleet may suspend or restrict an account where reasonably necessary
                    to address suspected compromise, misuse, security threats, legal
                    requirements, or violations of these Terms.
                </p>

                {{-- 4 --}}
                <h2 class="dark:text-white">
                    4. Acceptable Use
                </h2>

                <h3 class="dark:text-gray-200">
                    4.1 Permitted Use
                </h3>

                <p>
                    Subscribers may use the Service for legitimate rental, fleet,
                    customer-management, reporting, financial, administrative, and related
                    business purposes supported by KeyFleet.
                </p>

                <h3 class="dark:text-gray-200">
                    4.2 Prohibited Conduct
                </h3>

                <p>
                    You must not:
                </p>

                <ul>
                    <li>use the Service for unlawful, fraudulent, abusive, or deceptive activity;</li>
                    <li>attempt unauthorized access to accounts, systems, files, or networks;</li>
                    <li>upload malicious code, malware, scripts, or harmful files;</li>
                    <li>circumvent access controls or security protections;</li>
                    <li>interfere with the availability, security, or operation of the Service;</li>
                    <li>impersonate another individual or entity;</li>
                    <li>misrepresent your authority or affiliation;</li>
                    <li>scrape or harvest information except as expressly permitted;</li>
                    <li>use KeyFleet to violate another person's privacy or other legal rights;</li>
                    <li>upload personal data you have no lawful reason to possess or process;</li>
                    <li>sell, misuse, or disclose renter information for unauthorized purposes;</li>
                    <li>
                        collect passwords, PINs, one-time passwords, online banking
                        credentials, or similar authentication secrets through KeyFleet;
                    </li>
                    <li>
                        use renter IDs or verification documents for unrelated marketing,
                        unauthorized profiling, identity misuse, or other incompatible
                        purposes;
                    </li>
                    <li>use the Service in violation of applicable law.</li>
                </ul>

                {{-- 5 --}}
                <h2 class="dark:text-white">
                    5. Subscriber Responsibility for Personal Data
                </h2>

                <h3 class="dark:text-gray-200">
                    5.1 Subscriber-Controlled Personal Data
                </h3>

                <p>
                    Where a subscriber collects or enters personal data relating to renters,
                    customers, drivers, guarantors, employees, or other individuals, the
                    subscriber generally determines the purpose and means of that processing.
                </p>

                <p>
                    The subscriber is responsible for ensuring that its collection and use
                    of such information complies with applicable privacy and data protection
                    laws.
                </p>

                <h3 class="dark:text-gray-200">
                    5.2 Privacy Notices and Lawful Basis
                </h3>

                <p>
                    Subscribers are responsible for:
                </p>

                <ul>
                    <li>providing appropriate privacy notices to affected individuals;</li>
                    <li>having an appropriate lawful basis for processing;</li>
                    <li>obtaining valid consent where consent is required;</li>
                    <li>documenting processing activities where required;</li>
                    <li>honoring applicable data-subject rights;</li>
                    <li>using personal data only for legitimate and disclosed purposes.</li>
                </ul>

                <h3 class="dark:text-gray-200">
                    5.3 Data Minimization
                </h3>

                <p>
                    Subscribers must collect only information reasonably necessary for
                    legitimate rental and related business purposes.
                </p>

                <p>
                    KeyFleet must not be used as general-purpose storage for excessive,
                    unrelated, or unnecessary personal information.
                </p>

                {{-- 6 --}}
                <h2 class="dark:text-white">
                    6. Renter Requirements, IDs, and Uploaded Documents
                </h2>

                <p>
                    Certain KeyFleet features may allow subscribers or renters to upload
                    documents associated with rental verification or processing.
                </p>

                <p>
                    Such documents may include:
                </p>

                <ul>
                    <li>driver's licenses;</li>
                    <li>government-issued identification documents;</li>
                    <li>billing statements;</li>
                    <li>proof of address;</li>
                    <li>signed rental documentation;</li>
                    <li>other legitimate renter requirements.</li>
                </ul>

                <h3 class="dark:text-gray-200">
                    6.1 Subscriber Responsibilities
                </h3>

                <p>
                    Subscribers agree that they will:
                </p>

                <ul>
                    <li>request documents only where reasonably necessary;</li>
                    <li>communicate why the documents are being requested;</li>
                    <li>ensure an appropriate lawful basis exists for processing;</li>
                    <li>limit access to authorized personnel who require the information;</li>
                    <li>not retain documents longer than reasonably necessary;</li>
                    <li>not use documents for unrelated purposes;</li>
                    <li>not disclose the documents to unauthorized parties;</li>
                    <li>use available retention and permission controls responsibly.</li>
                </ul>

                <h3 class="dark:text-gray-200">
                    6.2 Prohibited Document Collection
                </h3>

                <p>
                    Subscribers must not intentionally request or store through KeyFleet:
                </p>

                <ul>
                    <li>online banking passwords;</li>
                    <li>banking PINs;</li>
                    <li>one-time passwords or authentication codes;</li>
                    <li>full login credentials for third-party services;</li>
                    <li>unrelated sensitive information having no legitimate rental purpose;</li>
                    <li>documents obtained unlawfully.</li>
                </ul>

                {{-- 7 --}}
                <h2 class="dark:text-white">
                    7. KeyFleet's Processing of Subscriber Data
                </h2>

                <h3 class="dark:text-gray-200">
                    7.1 Subscriber Data
                </h3>

                <p>
                    As between KeyFleet and the subscriber, the subscriber retains its
                    rights in information and content it lawfully submits to the Service,
                    subject to the rights of the individuals to whom personal data relates.
                </p>

                <h3 class="dark:text-gray-200">
                    7.2 Limited Processing Authorization
                </h3>

                <p>
                    The subscriber authorizes KeyFleet to access, host, store, organize,
                    transmit, reproduce, display, back up, and otherwise process subscriber
                    data only as reasonably necessary to:
                </p>

                <ul>
                    <li>provide and operate the Service;</li>
                    <li>perform requested features and integrations;</li>
                    <li>provide authorized support;</li>
                    <li>maintain security and prevent misuse;</li>
                    <li>perform backup, restoration, and maintenance;</li>
                    <li>comply with applicable law;</li>
                    <li>perform other processing expressly authorized by the subscriber.</li>
                </ul>

                <p>
                    This authorization does not transfer ownership of subscriber-controlled
                    information to KeyFleet.
                </p>

                <h3 class="dark:text-gray-200">
                    7.3 Processor Relationship
                </h3>

                <p>
                    Where KeyFleet processes renter or customer personal data solely on
                    behalf of a subscriber, KeyFleet generally acts as a Personal Information
                    Processor for that processing.
                </p>

                <p>
                    Additional data-processing terms may apply through a Data Processing
                    Agreement or Data Processing Addendum.
                </p>

                {{-- 8 --}}
                <h2 class="dark:text-white">
                    8. Access Control and Authorized Users
                </h2>

                <p>
                    Subscribers are responsible for determining which employees,
                    contractors, representatives, and other users may access their KeyFleet
                    account.
                </p>

                <p>
                    Subscribers must use appropriate roles and permissions and should grant
                    access according to legitimate business need.
                </p>

                <p>
                    Subscriber administrators are responsible for:
                </p>

                <ul>
                    <li>creating and disabling users;</li>
                    <li>assigning roles and permissions;</li>
                    <li>reviewing access where appropriate;</li>
                    <li>removing users who no longer require access;</li>
                    <li>protecting highly sensitive renter information from unnecessary access.</li>
                </ul>

                {{-- 9 --}}
                <h2 class="dark:text-white">
                    9. Subscription Plans, Billing, and Payments
                </h2>

                <h3 class="dark:text-gray-200">
                    9.1 Subscription Plans
                </h3>

                <p>
                    The Service is offered through subscription plans described on our
                    <a
                        href="/pricing"
                        class="text-blue-600 hover:underline dark:text-blue-400"
                    >
                        Pricing
                    </a>
                    page or through a separate written quotation or agreement.
                </p>

                <p>
                    Unless stated otherwise, fees are expressed in Philippine Pesos (PHP)
                    and may be subject to applicable taxes.
                </p>

                <h3 class="dark:text-gray-200">
                    9.2 Payment Processing
                </h3>

                <p>
                    Subscription payments may be processed through payment providers such
                    as PayMongo or another payment method made available by KeyFleet.
                </p>

                <p>
                    Payment information processed directly by a third-party provider is
                    also subject to that provider's terms and privacy practices.
                </p>

                <h3 class="dark:text-gray-200">
                    9.3 Manual Renewal
                </h3>

                <p>
                    Unless KeyFleet expressly introduces and discloses an automatic-renewal
                    feature, subscriptions are manually renewed.
                </p>

                <p>
                    This means:
                </p>

                <ul>
                    <li>the subscription does not automatically renew at expiration;</li>
                    <li>the subscriber must initiate the applicable renewal payment;</li>
                    <li>KeyFleet may send expiry or renewal reminders;</li>
                    <li>failure to renew may result in restricted or suspended access.</li>
                </ul>

                <h3 class="dark:text-gray-200">
                    9.4 Billing Cycles
                </h3>

                <p>
                    Depending on the selected plan, KeyFleet may offer billing periods such
                    as:
                </p>

                <ul>
                    <li>monthly;</li>
                    <li>three months;</li>
                    <li>six months;</li>
                    <li>twelve months;</li>
                    <li>another period expressly displayed or agreed upon.</li>
                </ul>

                <h3 class="dark:text-gray-200">
                    9.5 Pricing Changes
                </h3>

                <p>
                    KeyFleet may revise subscription prices from time to time.
                    Where reasonably appropriate or required, KeyFleet will provide notice
                    before material pricing changes take effect for future billing periods.
                </p>

                {{-- 10 --}}
                <h2 class="dark:text-white">
                    10. Trials, Refunds, and Billing Disputes
                </h2>

                <h3 class="dark:text-gray-200">
                    10.1 Free Trial
                </h3>

                <p>
                    If KeyFleet offers a free trial, the applicable duration and conditions
                    will be displayed at registration, on the Pricing page, or in another
                    applicable offer.
                </p>

                <h3 class="dark:text-gray-200">
                    10.2 Refunds
                </h3>

                <p>
                    Subscription payments are generally non-refundable after payment,
                    except where:
                </p>

                <ul>
                    <li>required by applicable law;</li>
                    <li>KeyFleet determines that a billing error occurred;</li>
                    <li>a separate written agreement expressly provides otherwise;</li>
                    <li>KeyFleet voluntarily approves a refund.</li>
                </ul>

                <h3 class="dark:text-gray-200">
                    10.3 Billing Concerns
                </h3>

                <p>
                    Subscribers should report suspected billing errors promptly and provide
                    sufficient information for KeyFleet to investigate the transaction.
                </p>

                {{-- 11 --}}
                <h2 class="dark:text-white">
                    11. Expiration, Suspension, and Termination
                </h2>

                <h3 class="dark:text-gray-200">
                    11.1 Subscription Expiration
                </h3>

                <p>
                    If a subscription expires without renewal, KeyFleet may restrict or
                    suspend access according to the applicable subscription rules.
                </p>

                <h3 class="dark:text-gray-200">
                    11.2 Suspension or Termination for Cause
                </h3>

                <p>
                    KeyFleet may suspend, restrict, or terminate access where reasonably
                    necessary because:
                </p>

                <ul>
                    <li>these Terms have been materially violated;</li>
                    <li>the Service is being used unlawfully;</li>
                    <li>the account presents a security risk;</li>
                    <li>fraud or abuse is reasonably suspected;</li>
                    <li>payment obligations remain unresolved;</li>
                    <li>KeyFleet is required to act by law or lawful authority;</li>
                    <li>continued use may materially harm KeyFleet, other users, or third parties.</li>
                </ul>

                <h3 class="dark:text-gray-200">
                    11.3 Data Following Termination
                </h3>

                <p>
                    Following account termination or subscription expiry, access to data may
                    be limited.
                </p>

                <p>
                    Where reasonably available and legally permitted, KeyFleet may provide
                    a limited period for a subscriber to export appropriate business data.
                </p>

                <p>
                    Data may thereafter be deleted or retained in accordance with the
                    Privacy Policy, applicable contractual obligations, legal requirements,
                    dispute-resolution requirements, security needs, and backup procedures.
                </p>

                {{-- 12 --}}
                <h2 class="dark:text-white">
                    12. Data Retention and Deletion
                </h2>

                <p>
                    Subscribers are responsible for determining appropriate retention periods
                    for renter information they control.
                </p>

                <p>
                    In particular, copies of:
                </p>

                <ul>
                    <li>government IDs;</li>
                    <li>driver's licenses;</li>
                    <li>billing statements;</li>
                    <li>proof-of-address documents;</li>
                    <li>other renter verification files;</li>
                </ul>

                <p>
                    should not be retained longer than reasonably necessary for the rental
                    transaction, legitimate dispute handling, fraud prevention, contractual
                    purposes, or applicable legal obligations.
                </p>

                <p>
                    Where KeyFleet provides retention controls, the subscriber is responsible
                    for selecting settings appropriate to its legitimate requirements.
                </p>

                {{-- 13 --}}
                <h2 class="dark:text-white">
                    13. Privacy and Data Protection
                </h2>

                <h3 class="dark:text-gray-200">
                    13.1 Privacy Policy
                </h3>

                <p>
                    KeyFleet's handling of personal information is further described in our
                    <a
                        href="{{ route('privacy-policy') }}"
                        class="text-blue-600 hover:underline dark:text-blue-400"
                    >
                        Privacy Policy
                    </a>.
                </p>

                <h3 class="dark:text-gray-200">
                    13.2 Subscriber Compliance
                </h3>

                <p>
                    Subscribers must comply with applicable privacy and data protection laws,
                    including applicable requirements under the Philippine Data Privacy Act
                    of 2012.
                </p>

                <h3 class="dark:text-gray-200">
                    13.3 Data Processing Agreement
                </h3>

                <p>
                    KeyFleet may require or provide additional Data Processing Terms or a
                    Data Processing Agreement governing processing performed on behalf of
                    subscribers.
                </p>

                <p>
                    Where such terms apply, they form part of the agreement between KeyFleet
                    and the subscriber.
                </p>

                {{-- 14 --}}
                <h2 class="dark:text-white">
                    14. Security
                </h2>

                <p>
                    KeyFleet seeks to maintain reasonable organizational, physical, and
                    technical safeguards appropriate to the Service and the information
                    being processed.
                </p>

                <p>
                    However, no software, network, storage system, or online transmission can
                    be guaranteed completely secure.
                </p>

                <p>
                    Subscribers remain responsible for:
                </p>

                <ul>
                    <li>protecting credentials;</li>
                    <li>managing their users;</li>
                    <li>assigning appropriate permissions;</li>
                    <li>maintaining security over their own devices and networks;</li>
                    <li>promptly reporting suspected unauthorized activity;</li>
                    <li>using KeyFleet's available security features responsibly.</li>
                </ul>

                {{-- 15 --}}
                <h2 class="dark:text-white">
                    15. Personal Data Breaches and Security Incidents
                </h2>

                <p>
                    If KeyFleet becomes aware of a security incident affecting subscriber
                    data, KeyFleet will investigate and take reasonable steps to contain,
                    mitigate, and respond to the incident.
                </p>

                <p>
                    Where KeyFleet acts as a Personal Information Processor, KeyFleet will
                    seek to provide the applicable subscriber with information reasonably
                    necessary for the subscriber to assess and satisfy applicable legal
                    notification obligations.
                </p>

                <p>
                    Where notification to the National Privacy Commission, affected
                    individuals, or another authority is legally required, the responsible
                    party will make such notification within the period required by
                    applicable law.
                </p>

                {{-- 16 --}}
                <h2 class="dark:text-white">
                    16. Support and Administrative Access
                </h2>

                <p>
                    KeyFleet personnel may access subscriber accounts or information only
                    where reasonably necessary for legitimate purposes such as:
                </p>

                <ul>
                    <li>responding to an authorized support request;</li>
                    <li>investigating technical problems;</li>
                    <li>maintaining or securing the Service;</li>
                    <li>investigating fraud or misuse;</li>
                    <li>complying with legal requirements;</li>
                    <li>other legitimate activities necessary to operate the Service.</li>
                </ul>

                <p>
                    Access to renter documents should be limited according to legitimate
                    business need.
                </p>

                {{-- 17 --}}
                <h2 class="dark:text-white">
                    17. Third-Party Services
                </h2>

                <p>
                    KeyFleet may integrate with or rely on third-party services, including
                    payment processors, hosting providers, communication services, and
                    other infrastructure providers.
                </p>

                <p>
                    Third-party services may have their own terms and privacy policies.
                    KeyFleet is not responsible for third-party acts outside KeyFleet's
                    reasonable control.
                </p>

                {{-- 18 --}}
                <h2 class="dark:text-white">
                    18. Service Availability and Changes
                </h2>

                <p>
                    KeyFleet may update, improve, modify, discontinue, or replace portions
                    of the Service from time to time.
                </p>

                <p>
                    We may perform maintenance or experience outages caused by maintenance,
                    infrastructure providers, network conditions, emergencies, security
                    events, force majeure, or circumstances outside our reasonable control.
                </p>

                <p>
                    KeyFleet does not guarantee uninterrupted or error-free operation of
                    every Service feature.
                </p>

                {{-- 19 --}}
                <h2 class="dark:text-white">
                    19. Subscriber Business Decisions
                </h2>

                <p>
                    KeyFleet may display reports, statuses, calculations, availability
                    information, analytics, reminders, or other business information.
                </p>

                <p>
                    Subscribers remain responsible for reviewing information before relying
                    on it for material rental, financial, legal, operational, or customer
                    decisions.
                </p>

                <p>
                    KeyFleet does not make rental approval, driver eligibility, insurance,
                    regulatory, or legal decisions on behalf of subscribers unless expressly
                    stated as part of a specific feature.
                </p>

                {{-- 20 --}}
                <h2 class="dark:text-white">
                    20. Intellectual Property
                </h2>

                <p>
                    KeyFleet and its licensors retain all rights in the Service, including
                    its software, interfaces, design, branding, documentation, and related
                    intellectual property, except for subscriber-owned content and other
                    third-party rights.
                </p>

                <p>
                    These Terms provide subscribers only the limited right to access and use
                    the Service during an authorized subscription.
                </p>

                {{-- 21 --}}
                <h2 class="dark:text-white">
                    21. Feedback
                </h2>

                <p>
                    If you voluntarily provide suggestions, feature requests, or feedback
                    about KeyFleet, you permit KeyFleet to use that feedback to develop,
                    improve, or operate the Service without an obligation to compensate you,
                    provided that KeyFleet does not thereby acquire ownership of your
                    confidential business information or personal data.
                </p>

                {{-- 22 --}}
                <h2 class="dark:text-white">
                    22. Disclaimer of Warranties
                </h2>

                <p>
                    To the maximum extent permitted by applicable law, the Service is
                    provided on an "as is" and "as available" basis.
                </p>

                <p>
                    KeyFleet does not warrant that:
                </p>

                <ul>
                    <li>the Service will always be uninterrupted;</li>
                    <li>every defect will be corrected immediately;</li>
                    <li>every feature will meet every subscriber's individual requirements;</li>
                    <li>the Service will eliminate all business, privacy, security, fraud, or operational risks.</li>
                </ul>

                <p>
                    Nothing in these Terms excludes warranties, rights, or protections that
                    cannot lawfully be excluded.
                </p>

                {{-- 23 --}}
                <h2 class="dark:text-white">
                    23. Limitation of Liability
                </h2>

                <p>
                    To the maximum extent permitted by applicable law, KeyFleet will not be
                    liable for indirect, incidental, consequential, exemplary, or special
                    damages arising from use of the Service, including loss of profits,
                    revenue, goodwill, or business opportunity, except where liability
                    cannot legally be excluded.
                </p>

                <p>
                    KeyFleet is not responsible for losses caused by:
                </p>

                <ul>
                    <li>subscriber rental decisions;</li>
                    <li>subscriber pricing or refund decisions;</li>
                    <li>subscriber misuse of renter information;</li>
                    <li>unauthorized access resulting from subscriber credential compromise;</li>
                    <li>subscriber configuration errors;</li>
                    <li>third-party services outside KeyFleet's reasonable control;</li>
                    <li>events outside KeyFleet's reasonable control.</li>
                </ul>

                <p>
                    Any specific liability cap applicable to a subscriber may be set out in
                    the applicable subscription agreement, quotation, Data Processing
                    Agreement, or other written agreement.
                </p>

                {{-- 24 --}}
                <h2 class="dark:text-white">
                    24. Indemnity
                </h2>

                <p>
                    To the extent permitted by law, a subscriber agrees to be responsible
                    for claims, losses, liabilities, penalties, and reasonable costs arising
                    from its unlawful use of the Service, violation of these Terms, or
                    unlawful collection, use, or disclosure of personal data under its
                    control.
                </p>

                <p>
                    This provision does not apply to the extent that the claim was caused by
                    KeyFleet's own unlawful act, breach of applicable obligations, or conduct
                    for which liability cannot legally be excluded.
                </p>

                {{-- 25 --}}
                <h2 class="dark:text-white">
                    25. Agent Program
                </h2>

                <p>
                    Participation in the KeyFleet Agent Program may be subject to separate
                    Agent Program Terms, commission rules, payout policies, eligibility
                    requirements, and referral attribution rules.
                </p>

                <p>
                    Unless expressly stated otherwise, Agent commissions are not rental
                    commissions and are not earned from individual vehicle rental
                    transactions.
                </p>

                <p>
                    Agent Program terms control where they conflict with these general Terms
                    regarding Agent-specific matters.
                </p>

                {{-- 26 --}}
                <h2 class="dark:text-white">
                    26. Assignment
                </h2>

                <p>
                    A subscriber may not assign or transfer material rights or obligations
                    under these Terms without KeyFleet's prior written consent, except where
                    otherwise permitted by applicable law or a written agreement.
                </p>

                <p>
                    KeyFleet may assign these Terms in connection with a lawful merger,
                    restructuring, acquisition, financing, or transfer of the business,
                    subject to applicable law.
                </p>

                {{-- 27 --}}
                <h2 class="dark:text-white">
                    27. Changes to These Terms
                </h2>

                <p>
                    KeyFleet may update these Terms from time to time.
                </p>

                <p>
                    For material changes, KeyFleet may provide notice through:
                </p>

                <ul>
                    <li>the Service;</li>
                    <li>email;</li>
                    <li>the KeyFleet website;</li>
                    <li>another reasonable communication method.</li>
                </ul>

                <p>
                    Material changes will take effect on the stated effective date.
                    Continued use of the Service after that date may constitute acceptance
                    of the updated Terms where permitted by law.
                </p>

                <p>
                    Changes to privacy practices remain subject to the Privacy Policy and
                    applicable privacy law.
                </p>

                {{-- 28 --}}
                <h2 class="dark:text-white">
                    28. Governing Law and Disputes
                </h2>

                <p>
                    These Terms are governed by the laws of the Republic of the Philippines,
                    without prejudice to mandatory rights or protections that cannot
                    lawfully be waived.
                </p>

                <p>
                    The parties should first attempt in good faith to resolve disputes
                    through direct communication before commencing formal proceedings,
                    except where urgent legal or protective relief is reasonably necessary.
                </p>

                <p>
                    Subject to applicable law and any separate written agreement, disputes
                    arising from these Terms may be submitted to courts of competent
                    jurisdiction in the Philippines.
                </p>

                {{-- 29 --}}
                <h2 class="dark:text-white">
                    29. Severability
                </h2>

                <p>
                    If any provision of these Terms is found unenforceable or invalid, the
                    remaining provisions will continue in effect to the extent permitted by
                    law.
                </p>

                {{-- 30 --}}
                <h2 class="dark:text-white">
                    30. Entire Agreement
                </h2>

                <p>
                    These Terms, together with the Privacy Policy and any applicable
                    subscription agreement, quotation, Data Processing Agreement,
                    Agent Program Terms, or other expressly incorporated terms, constitute
                    the applicable agreement between KeyFleet and the subscriber regarding
                    the Service.
                </p>

                {{-- 31 --}}
                <h2 class="dark:text-white">
                    31. Contact Information
                </h2>

                <div
                    class="mt-6 rounded-2xl border border-gray-200 bg-gray-50 p-6 dark:border-gray-700 dark:bg-gray-800"
                >
                    <h3 class="mt-0 text-gray-900 dark:text-white">
                        KeyFleet Support
                    </h3>

                    <p class="dark:text-gray-300">
                        If you have questions about these Terms or the Service, contact us:
                    </p>

                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        <strong>Email:</strong>

                        <a
                            href="mailto:support@keyfleet.com"
                            class="text-blue-600 hover:underline dark:text-blue-400"
                        >
                            support@keyfleet.com
                        </a>

                        <br>

                        <strong>Privacy:</strong>

                        <a
                            href="mailto:support@keyfleet.com"
                            class="text-blue-600 hover:underline dark:text-blue-400"
                        >
                            support@keyfleet.com
                        </a>

                        <br>

                        <strong>Phone:</strong>

                        <a
                            href="tel:+639195438297"
                            class="text-blue-600 hover:underline dark:text-blue-400"
                        >
                            +63 (919) 543-8297
                        </a>

                        <br>

                        <strong>Address:</strong>
                        San Isidro Norte, Santo Tomas, Batangas, Philippines
                    </p>
                </div>
            </article>
        </div>
    </section>
</x-layouts.guest>