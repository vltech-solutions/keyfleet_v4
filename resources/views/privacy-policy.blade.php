<x-layouts.guest>
    @php
        /*
         * IMPORTANT:
         * Update this manually whenever the Privacy Policy materially changes.
         * Do not use today's date automatically because that would make the
         * policy appear to change every day.
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
                        Privacy & Data Protection
                    </span>

                    <span class="text-xs text-gray-400 dark:text-gray-500">
                        Version {{ $version }}
                    </span>
                </div>

                <h1 class="mt-4 text-4xl font-extrabold text-gray-900 dark:text-white">
                    Privacy Policy
                </h1>

                <p class="mt-2 text-gray-500 dark:text-gray-400">
                    Last Updated: {{ $lastUpdated }}
                </p>
            </div>

            {{-- Quick Summary --}}
            <div
                class="mb-10 rounded-2xl border border-blue-200 bg-blue-50 p-5 text-sm text-blue-800 dark:border-blue-800 dark:bg-blue-900/20 dark:text-blue-200"
            >
                <strong class="block text-base">
                    Privacy at a glance
                </strong>

                <ul class="mt-3 list-inside list-disc space-y-1.5">
                    <li>We process personal data only for legitimate and disclosed purposes.</li>
                    <li>We do not sell personal information.</li>
                    <li>KeyFleet subscribers remain responsible for how they collect and use renter information.</li>
                    <li>KeyFleet may process renter information on behalf of subscribers to provide the platform.</li>
                    <li>Renter records may include uploaded requirements such as IDs and proof-of-address documents.</li>
                    <li>Individuals have privacy rights provided by applicable Philippine law.</li>
                    <li>Access, deletion, correction, and other requests may be subject to legal and legitimate retention requirements.</li>
                </ul>
            </div>

            <article class="prose max-w-none text-gray-700 dark:text-gray-300 dark:prose-invert">
                {{-- Introduction --}}
                <div
                    class="mb-8 rounded-2xl border border-gray-200 bg-gray-50 p-6 dark:border-gray-700 dark:bg-gray-800"
                >
                    <h2 class="mt-0 text-gray-900 dark:text-white">
                        Our Commitment to Privacy
                    </h2>

                    <p class="mb-0 dark:text-gray-300">
                        <strong>KeyFleet</strong> is a software platform that helps car rental
                        businesses manage vehicles, customers, bookings, payments, documents,
                        and related rental operations.
                    </p>

                    <p class="mb-0 dark:text-gray-300">
                        This Privacy Policy explains how KeyFleet collects, uses, stores,
                        discloses, and otherwise processes personal data in connection with
                        our website, applications, portals, and related services
                        (collectively, the <strong>"Service"</strong>).
                    </p>

                    <p class="mb-0 dark:text-gray-300">
                        We seek to process personal data in accordance with applicable
                        Philippine privacy laws, including Republic Act No. 10173,
                        otherwise known as the Data Privacy Act of 2012, its implementing
                        rules and regulations, and applicable issuances of the National
                        Privacy Commission.
                    </p>
                </div>

                {{-- 1 --}}
                <h2 class="dark:text-white">
                    1. Who This Privacy Policy Applies To
                </h2>

                <p>
                    This Privacy Policy may apply to:
                </p>

                <ul>
                    <li>KeyFleet subscribers and their authorized users;</li>
                    <li>prospective subscribers;</li>
                    <li>KeyFleet Agents and referral partners;</li>
                    <li>visitors to our public website;</li>
                    <li>
                        renters, drivers, customers, guarantors, representatives, or other
                        individuals whose information is processed through a subscriber's
                        KeyFleet account;
                    </li>
                    <li>individuals who contact KeyFleet for support or other inquiries.</li>
                </ul>

                {{-- 2 --}}
                <h2 class="dark:text-white">
                    2. KeyFleet and Subscriber Privacy Responsibilities
                </h2>

                <h3 class="dark:text-gray-200">
                    2.1 KeyFleet as Personal Information Controller
                </h3>

                <p>
                    KeyFleet may act as a Personal Information Controller when we determine
                    the purpose and means of processing personal data for our own legitimate
                    business purposes, including:
                </p>

                <ul>
                    <li>creating and administering KeyFleet accounts;</li>
                    <li>managing subscriptions and billing;</li>
                    <li>operating the KeyFleet Agent Program;</li>
                    <li>providing support and communicating with users;</li>
                    <li>protecting the Service against fraud, misuse, and security threats;</li>
                    <li>maintaining business, accounting, and compliance records;</li>
                    <li>improving and administering the Service.</li>
                </ul>

                <h3 class="dark:text-gray-200">
                    2.2 Subscriber as Personal Information Controller
                </h3>

                <p>
                    A car rental business using KeyFleet generally determines why renter,
                    customer, driver, and rental-related information is collected and how
                    that information will be used in its rental operations.
                </p>

                <p>
                    Accordingly, the subscriber generally acts as the Personal Information
                    Controller for personal data it collects from or about its customers,
                    renters, drivers, guarantors, employees, and other individuals.
                </p>

                <p>
                    Subscribers are responsible for:
                </p>

                <ul>
                    <li>having an appropriate lawful basis for processing personal data;</li>
                    <li>providing appropriate privacy notices;</li>
                    <li>obtaining consent where consent is required by law;</li>
                    <li>collecting only information reasonably necessary for legitimate purposes;</li>
                    <li>restricting access to authorized personnel;</li>
                    <li>responding to applicable data-subject requests;</li>
                    <li>complying with applicable privacy and data protection requirements.</li>
                </ul>

                <h3 class="dark:text-gray-200">
                    2.3 KeyFleet as Personal Information Processor
                </h3>

                <p>
                    Where a subscriber uses KeyFleet to collect, store, organize, access,
                    transmit, or otherwise process renter or customer information,
                    KeyFleet generally processes that information on behalf of the subscriber
                    in order to provide the Service.
                </p>

                <p>
                    In these circumstances, KeyFleet generally acts as a Personal Information
                    Processor and processes the information according to the subscriber's
                    use of the Service, applicable contractual terms, documented instructions,
                    and applicable law.
                </p>

                <div
                    class="my-6 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-900 dark:bg-amber-950/30 dark:text-amber-200"
                >
                    <strong>For renters:</strong>
                    if you provided your information directly to a car rental company using
                    KeyFleet, that car rental company is generally the appropriate first
                    contact for questions about why your information was collected or how
                    it is being used.
                </div>

                {{-- 3 --}}
                <h2 class="dark:text-white">
                    3. Personal Data We May Process
                </h2>

                <h3 class="dark:text-gray-200">
                    3.1 Account and User Information
                </h3>

                <ul>
                    <li>name;</li>
                    <li>email address;</li>
                    <li>mobile or telephone number;</li>
                    <li>username and authentication information;</li>
                    <li>company or organization affiliation;</li>
                    <li>role, permissions, and account settings;</li>
                    <li>communications and support requests.</li>
                </ul>

                <h3 class="dark:text-gray-200">
                    3.2 Business and Subscriber Information
                </h3>

                <ul>
                    <li>business name;</li>
                    <li>business address;</li>
                    <li>business contact information;</li>
                    <li>tax or registration information where provided;</li>
                    <li>fleet and vehicle information;</li>
                    <li>branches and operating locations;</li>
                    <li>subscription plan and account status;</li>
                    <li>billing and transaction records.</li>
                </ul>

                <h3 class="dark:text-gray-200">
                    3.3 Renter and Customer Information
                </h3>

                <p>
                    Depending on the subscriber's configuration and rental requirements,
                    information processed through KeyFleet may include:
                </p>

                <ul>
                    <li>full name;</li>
                    <li>email address and telephone number;</li>
                    <li>residential or billing address;</li>
                    <li>date of birth where legitimately required;</li>
                    <li>booking and rental information;</li>
                    <li>pickup and return information;</li>
                    <li>driver information;</li>
                    <li>emergency contact details where collected;</li>
                    <li>payment and reservation records;</li>
                    <li>rental agreements, receipts, invoices, and related documents;</li>
                    <li>notes or information entered by the subscriber in connection with the rental.</li>
                </ul>

                <h3 class="dark:text-gray-200">
                    3.4 Uploaded Identification and Verification Requirements
                </h3>

                <p>
                    Subscribers may configure KeyFleet to allow renters or authorized users
                    to upload documents required for legitimate rental verification or
                    transaction purposes.
                </p>

                <p>
                    These documents may include:
                </p>

                <ul>
                    <li>driver's licenses;</li>
                    <li>government-issued identification documents;</li>
                    <li>proof-of-address documents;</li>
                    <li>billing statements;</li>
                    <li>photographs or images of required documents;</li>
                    <li>signed rental-related forms;</li>
                    <li>other verification documents legitimately required by the rental business.</li>
                </ul>

                <p>
                    These documents may contain personal data and, depending on their content,
                    information that is subject to additional protection under applicable
                    privacy laws.
                </p>

                <div
                    class="my-6 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800 dark:border-red-900 dark:bg-red-950/30 dark:text-red-200"
                >
                    <strong>Do not upload unnecessary information.</strong>
                    Subscribers and renters should provide only documents and information
                    reasonably necessary for the applicable rental transaction. Passwords,
                    PINs, one-time passwords, online banking credentials, or unrelated
                    financial credentials must not be uploaded to KeyFleet.
                </div>

                <h3 class="dark:text-gray-200">
                    3.5 Payment Information
                </h3>

                <p>
                    KeyFleet may process subscription billing information and payment
                    transaction records. Where a third-party payment provider such as
                    PayMongo is used, payment credentials handled directly by that provider
                    are subject to the provider's own privacy and security practices.
                </p>

                <p>
                    KeyFleet does not intend to store complete payment-card credentials
                    when payments are processed directly by the applicable payment provider.
                </p>

                <h3 class="dark:text-gray-200">
                    3.6 Technical and Usage Information
                </h3>

                <p>
                    We may automatically collect:
                </p>

                <ul>
                    <li>IP address;</li>
                    <li>browser type;</li>
                    <li>device and operating-system information;</li>
                    <li>login and security events;</li>
                    <li>usage information;</li>
                    <li>approximate location derived from IP address;</li>
                    <li>cookies and similar technologies;</li>
                    <li>audit and activity logs.</li>
                </ul>

                <h3 class="dark:text-gray-200">
                    3.7 Information From Third Parties
                </h3>

                <p>
                    We may receive information from:
                </p>

                <ul>
                    <li>payment processors;</li>
                    <li>services connected or integrated with KeyFleet;</li>
                    <li>referral and Agent Program participants;</li>
                    <li>authorized business partners;</li>
                    <li>other sources where collection is permitted by law.</li>
                </ul>

                {{-- 4 --}}
                <h2 class="dark:text-white">
                    4. Why We Process Personal Data
                </h2>

                <p>
                    Depending on the context, personal data may be processed for purposes
                    including:
                </p>

                <ul>
                    <li>creating and administering accounts;</li>
                    <li>providing the KeyFleet platform;</li>
                    <li>processing rental inquiries, bookings, and reservations;</li>
                    <li>allowing subscribers to verify renter requirements;</li>
                    <li>managing vehicles and rental operations;</li>
                    <li>generating invoices, receipts, rental documents, and reports;</li>
                    <li>processing subscription payments;</li>
                    <li>administering referrals and Agent commissions;</li>
                    <li>providing customer support;</li>
                    <li>maintaining security and preventing fraud or abuse;</li>
                    <li>maintaining audit logs and transaction records;</li>
                    <li>complying with legal and regulatory requirements;</li>
                    <li>protecting the rights, property, and security of users and KeyFleet;</li>
                    <li>improving and maintaining the Service.</li>
                </ul>

                {{-- 5 --}}
                <h2 class="dark:text-white">
                    5. Lawful Bases and Subscriber Responsibility
                </h2>

                <p>
                    The lawful basis applicable to a particular processing activity depends
                    on the circumstances, the type of information involved, the purpose of
                    processing, and applicable law.
                </p>

                <p>
                    Processing may, where appropriate, be based on:
                </p>

                <ul>
                    <li>performance of a contract or steps requested before entering into a contract;</li>
                    <li>compliance with a legal obligation;</li>
                    <li>a legitimate purpose or interest permitted by applicable law;</li>
                    <li>consent where consent is required or appropriate;</li>
                    <li>another lawful basis permitted under applicable law.</li>
                </ul>

                <p>
                    KeyFleet does not represent that consent is the appropriate lawful basis
                    for every processing activity.
                </p>

                <p>
                    Subscribers remain responsible for identifying and documenting the
                    appropriate lawful basis for the personal data they collect through
                    their rental operations.
                </p>

                {{-- 6 --}}
                <h2 class="dark:text-white">
                    6. Renter Documents and Verification Information
                </h2>

                <p>
                    Uploaded renter requirements are intended to be used only for legitimate
                    rental-related purposes, such as:
                </p>

                <ul>
                    <li>identity verification;</li>
                    <li>driver eligibility verification;</li>
                    <li>proof-of-address verification;</li>
                    <li>booking review and approval;</li>
                    <li>rental documentation;</li>
                    <li>fraud prevention;</li>
                    <li>dispute handling;</li>
                    <li>other legitimate requirements associated with the rental transaction.</li>
                </ul>

                <p>
                    Subscribers must not use renter documents obtained through KeyFleet for
                    unrelated marketing, unauthorized profiling, identity misuse, resale,
                    or any other purpose that is incompatible with the purpose for which
                    the information was collected.
                </p>

                {{-- 7 --}}
                <h2 class="dark:text-white">
                    7. When Personal Data May Be Disclosed
                </h2>

                <p>
                    <strong>KeyFleet does not sell personal information.</strong>
                </p>

                <p>
                    Personal data may be disclosed when reasonably necessary to:
                </p>

                <ul>
                    <li>
                        <strong>Subscribers and Authorized Users:</strong>
                        where information relates to their rental operations and they are
                        authorized to access it;
                    </li>

                    <li>
                        <strong>Service Providers and Subprocessors:</strong>
                        providers supporting hosting, infrastructure, payments,
                        communications, security, monitoring, support, or other necessary
                        platform functions;
                    </li>

                    <li>
                        <strong>Payment Providers:</strong>
                        where necessary to process applicable transactions;
                    </li>

                    <li>
                        <strong>Professional Advisers:</strong>
                        such as legal, accounting, security, or compliance advisers where
                        reasonably necessary;
                    </li>

                    <li>
                        <strong>Authorities:</strong>
                        where required by applicable law, lawful court order, regulatory
                        request, or other legally valid process;
                    </li>

                    <li>
                        <strong>Business Transfers:</strong>
                        in connection with a lawful merger, acquisition, restructuring,
                        financing, or transfer of business assets, subject to applicable
                        protections;
                    </li>

                    <li>
                        <strong>With Authorization:</strong>
                        where the applicable individual or controller has authorized the
                        disclosure.
                    </li>
                </ul>

                {{-- 8 --}}
                <h2 class="dark:text-white">
                    8. Service Providers and Subprocessors
                </h2>

                <p>
                    KeyFleet may engage third-party providers that process information
                    necessary to deliver the Service.
                </p>

                <p>
                    We seek to select providers appropriate to the nature of the services
                    involved and require appropriate privacy, confidentiality, and security
                    commitments where applicable.
                </p>

                <p>
                    Subscribers acknowledge that providing a cloud-based Service may require
                    the use of hosting, payment, communications, monitoring, and other
                    infrastructure providers.
                </p>

                {{-- 9 --}}
                <h2 class="dark:text-white">
                    9. International Processing and Data Transfers
                </h2>

                <p>
                    Some service providers used to operate KeyFleet may process or store
                    information outside the Philippines.
                </p>

                <p>
                    Where cross-border processing occurs, KeyFleet will seek to implement
                    safeguards appropriate to the nature of the information, applicable
                    contractual arrangements, and applicable privacy requirements.
                </p>

                {{-- 10 --}}
                <h2 class="dark:text-white">
                    10. Cookies and Similar Technologies
                </h2>

                <p>
                    KeyFleet may use cookies or similar technologies for:
                </p>

                <ul>
                    <li>
                        <strong>Essential purposes:</strong>
                        authentication, session management, security, and core functionality;
                    </li>

                    <li>
                        <strong>Functional purposes:</strong>
                        remembering settings and preferences;
                    </li>

                    <li>
                        <strong>Analytics:</strong>
                        understanding platform performance and usage where implemented;
                    </li>

                    <li>
                        <strong>Marketing:</strong>
                        where such technologies are implemented and any required consent has
                        been obtained.
                    </li>
                </ul>

                <p>
                    Browser settings may allow you to restrict certain cookies.
                    Disabling essential cookies may prevent parts of the Service from
                    functioning correctly.
                </p>

                {{-- 11 --}}
                <h2 class="dark:text-white">
                    11. Data Retention
                </h2>

                <p>
                    Personal data should not be retained longer than reasonably necessary
                    for the purpose for which it was collected, subject to legitimate
                    business, contractual, security, dispute-resolution, accounting,
                    regulatory, and legal requirements.
                </p>

                <h3 class="dark:text-gray-200">
                    11.1 Account and Subscriber Records
                </h3>

                <p>
                    KeyFleet may retain subscriber and account information while an account
                    remains active and for a reasonable period following termination where
                    necessary for account closure, recovery, legal compliance, disputes,
                    accounting, fraud prevention, or other legitimate purposes.
                </p>

                <h3 class="dark:text-gray-200">
                    11.2 Renter and Booking Records
                </h3>

                <p>
                    Retention of renter and booking information may depend on the subscriber's
                    legitimate rental, contractual, accounting, legal, and dispute-resolution
                    requirements.
                </p>

                <p>
                    Subscribers are responsible for establishing appropriate retention periods
                    for personal data they control.
                </p>

                <h3 class="dark:text-gray-200">
                    11.3 Uploaded IDs and Verification Documents
                </h3>

                <p>
                    Identification and verification documents should be retained only for as
                    long as reasonably necessary for the applicable rental transaction,
                    verification, fraud prevention, dispute resolution, contractual
                    requirements, or applicable legal obligations.
                </p>

                <p>
                    Where retention controls are made available by KeyFleet, subscribers
                    should configure them according to their lawful operational requirements.
                </p>

                <h3 class="dark:text-gray-200">
                    11.4 Backups and Technical Copies
                </h3>

                <p>
                    Deleted information may remain temporarily in backups, logs, or recovery
                    systems until those copies are overwritten or securely removed according
                    to applicable operational procedures.
                </p>

                {{-- 12 --}}
                <h2 class="dark:text-white">
                    12. Security
                </h2>

                <p>
                    KeyFleet seeks to maintain reasonable and appropriate organizational,
                    physical, and technical safeguards considering the nature of the
                    information processed and associated risks.
                </p>

                <p>
                    Measures may include, where applicable:
                </p>

                <ul>
                    <li>authentication and authorization controls;</li>
                    <li>role-based or permission-based access restrictions;</li>
                    <li>tenant and account isolation controls;</li>
                    <li>secure transmission of data;</li>
                    <li>restricted administrative access;</li>
                    <li>logging and monitoring;</li>
                    <li>secure software-development practices;</li>
                    <li>file-upload validation and restrictions;</li>
                    <li>backup and recovery procedures;</li>
                    <li>security incident-response procedures.</li>
                </ul>

                <p>
                    No online system can be guaranteed to be completely secure.
                    Users and subscribers must also maintain appropriate security over their
                    own accounts, devices, passwords, users, and permissions.
                </p>

                {{-- 13 --}}
                <h2 class="dark:text-white">
                    13. Access to Uploaded Renter Documents
                </h2>

                <p>
                    Subscriber users should access renter documents only where necessary for
                    legitimate rental-related duties.
                </p>

                <p>
                    Subscribers are responsible for assigning appropriate user roles and
                    permissions and removing access when it is no longer required.
                </p>

                <p>
                    KeyFleet personnel should not access subscriber-controlled renter
                    information except where reasonably necessary for authorized support,
                    security, maintenance, legal compliance, investigation of misuse, or
                    another legitimate platform purpose.
                </p>

                {{-- 14 --}}
                <h2 class="dark:text-white">
                    14. Personal Data Breaches and Security Incidents
                </h2>

                <p>
                    KeyFleet maintains procedures intended to identify, investigate, contain,
                    mitigate, and respond to security incidents affecting personal data.
                </p>

                <p>
                    Where a personal data breach is subject to mandatory notification under
                    applicable law, KeyFleet and/or the applicable Personal Information
                    Controller will take steps to provide required notifications to the
                    National Privacy Commission and affected data subjects within the
                    period required by law.
                </p>

                <p>
                    Where KeyFleet acts as a processor for subscriber-controlled data,
                    KeyFleet will seek to notify and cooperate with the applicable subscriber
                    as reasonably necessary for the subscriber to satisfy its legal
                    obligations.
                </p>

                {{-- 15 --}}
                <h2 class="dark:text-white">
                    15. Your Privacy Rights
                </h2>

                <p>
                    Subject to applicable law and the circumstances of the processing,
                    individuals may have rights including:
                </p>

                <ul>
                    <li>the right to be informed;</li>
                    <li>the right to access personal data;</li>
                    <li>the right to object to certain processing;</li>
                    <li>the right to correct inaccurate or incomplete data;</li>
                    <li>the right to erasure or blocking where legally available;</li>
                    <li>the right to damages where provided by law;</li>
                    <li>the right to data portability where applicable;</li>
                    <li>the right to lodge a complaint with the National Privacy Commission;</li>
                    <li>other rights provided under applicable privacy law.</li>
                </ul>

                <p>
                    These rights are not absolute and may be subject to lawful limitations,
                    including legal retention duties, contractual requirements,
                    establishment or defense of legal claims, security requirements, and
                    other grounds allowed by law.
                </p>

                {{-- 16 --}}
                <h2 class="dark:text-white">
                    16. Exercising Privacy Rights
                </h2>

                <h3 class="dark:text-gray-200">
                    If You Are a KeyFleet Subscriber, User, or Agent
                </h3>

                <p>
                    You may contact KeyFleet regarding personal data that KeyFleet controls
                    directly.
                </p>

                <h3 class="dark:text-gray-200">
                    If You Are a Renter or Customer of a KeyFleet Subscriber
                </h3>

                <p>
                    If your personal information was collected by a car rental business
                    using KeyFleet, please contact that business first because it generally
                    determines why your renter information is processed.
                </p>

                <p>
                    KeyFleet may assist the subscriber in responding to valid requests where
                    appropriate and permitted.
                </p>

                <p>
                    Before fulfilling certain requests, identity verification may be required
                    to protect personal data from unauthorized disclosure.
                </p>

                {{-- 17 --}}
                <h2 class="dark:text-white">
                    17. Children and Minors
                </h2>

                <p>
                    KeyFleet is intended primarily for businesses and individuals legally
                    able to engage in applicable rental and contractual activities.
                </p>

                <p>
                    Subscribers must not knowingly use KeyFleet to collect personal
                    information from minors unless the collection is lawful, necessary,
                    properly disclosed, and subject to any required consent or authorization.
                </p>

                {{-- 18 --}}
                <h2 class="dark:text-white">
                    18. Third-Party Websites and Services
                </h2>

                <p>
                    The Service may contain links to third-party websites or services.
                    Their privacy practices are governed by their own policies and are not
                    controlled by this Privacy Policy.
                </p>

                {{-- 19 --}}
                <h2 class="dark:text-white">
                    19. Changes to This Privacy Policy
                </h2>

                <p>
                    We may update this Privacy Policy to reflect changes in the Service,
                    business practices, legal requirements, security requirements, or
                    processing activities.
                </p>

                <p>
                    For material changes, we may provide notice through appropriate channels,
                    which may include:
                </p>

                <ul>
                    <li>the KeyFleet website;</li>
                    <li>email;</li>
                    <li>an in-app notification;</li>
                    <li>another reasonable communication method.</li>
                </ul>

                <p>
                    Where applicable law requires consent before beginning a new or materially
                    different processing activity, we will seek the required consent before
                    undertaking that processing.
                </p>

                <p>
                    The "Last Updated" date at the top of this page indicates when this
                    Privacy Policy was last materially revised.
                </p>

                {{-- 20 --}}
                <h2 class="dark:text-white">
                    20. Contact Us
                </h2>

                <div
                    class="mt-6 rounded-2xl border border-gray-200 bg-gray-50 p-6 dark:border-gray-700 dark:bg-gray-800"
                >
                    <h3 class="mt-0 text-gray-900 dark:text-white">
                        Privacy Questions or Requests
                    </h3>

                    <p class="dark:text-gray-300">
                        For questions about this Privacy Policy, privacy concerns, or
                        requests involving personal data processed directly by KeyFleet,
                        contact us:
                    </p>

                    <div class="mt-4">
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            <strong>KeyFleet Privacy</strong>
                            <br>

                            <strong>Email:</strong>
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

                    <p class="mb-0 mt-4 text-sm text-gray-500 dark:text-gray-400">
                        Renters whose information was collected by a car rental business
                        using KeyFleet should generally contact that rental business first
                        regarding renter-specific privacy requests.
                    </p>
                </div>

                {{-- Disclaimer --}}
                <div
                    class="mt-8 rounded-xl border border-gray-200 bg-white p-4 text-xs leading-5 text-gray-500 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-400"
                >
                    This Privacy Policy describes KeyFleet's general privacy practices.
                    Specific subscriber arrangements, Data Processing Agreements, applicable
                    laws, or circumstances may impose additional obligations.
                </div>
            </article>
        </div>
    </section>
</x-layouts.guest>