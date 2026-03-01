<footer class="bg-white dark:bg-zinc-900 border-t border-zinc-200 dark:border-zinc-800 pt-16 pb-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-16">
            <!-- Brand & Description -->
            <div class="md:col-span-1">
                <a href="{{ home_url('/') }}"
                    class="text-xl font-bold text-zinc-900 dark:text-white flex items-center gap-2 mb-4">
                    <flux:icon.rocket-launch class="w-6 h-6 text-indigo-500" />
                    <span>{!! $siteName !!}</span>
                </a>
                <p class="text-sm text-zinc-600 dark:text-zinc-400 mb-6">
                    A beautiful, modular platform built intelligently to help your business scale effortlessly.
                </p>
                <div class="flex items-center gap-4 text-zinc-400">
                    <a href="#"
                        class="hover:text-zinc-900 dark:hover:text-white transition-colors"><flux:icon.globe-alt
                            class="w-5 h-5" /></a>
                    <!-- Flux Icon defaults to Heroicons, we can use code-bracket instead of github if github is not imported, but let's assume we have it or use simple ones -->
                    <a href="#"
                        class="hover:text-zinc-900 dark:hover:text-white transition-colors"><flux:icon.code-bracket
                            class="w-5 h-5" /></a>
                    <a href="#" class="hover:text-zinc-900 dark:hover:text-white transition-colors">
                        <flux:icon.envelope class="w-5 h-5" />
                    </a>
                </div>
            </div>

            <!-- Product -->
            <div>
                <h3 class="text-sm font-bold text-zinc-900 dark:text-white uppercase tracking-wider mb-4">Product</h3>
                <ul class="space-y-3">
                    <li><a href="#"
                            class="text-sm text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors">Features</a>
                    </li>
                    <li><a href="#"
                            class="text-sm text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors">Integrations</a>
                    </li>
                    <li><a href="#"
                            class="text-sm text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors">Pricing</a>
                    </li>
                    <li><a href="#"
                            class="text-sm text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors">Changelog</a>
                    </li>
                </ul>
            </div>

            <!-- Company -->
            <div>
                <h3 class="text-sm font-bold text-zinc-900 dark:text-white uppercase tracking-wider mb-4">Company</h3>
                <ul class="space-y-3">
                    <li><a href="#"
                            class="text-sm text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors">About
                            Us</a></li>
                    <li><a href="#"
                            class="text-sm text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors">Careers</a>
                    </li>
                    <li><a href="#"
                            class="text-sm text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors">Blog</a>
                    </li>
                    <li><a href="#"
                            class="text-sm text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors">Contact</a>
                    </li>
                </ul>
            </div>

            <!-- Legal -->
            <div>
                <h3 class="text-sm font-bold text-zinc-900 dark:text-white uppercase tracking-wider mb-4">Legal</h3>
                <ul class="space-y-3">
                    <li><a href="#"
                            class="text-sm text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors">Privacy
                            Policy</a></li>
                    <li><a href="#"
                            class="text-sm text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors">Terms
                            of Service</a></li>
                    <li><a href="#"
                            class="text-sm text-zinc-600 dark:text-zinc-400 hover:text-zinc-900 dark:hover:text-white transition-colors">Cookie
                            Policy</a></li>
                </ul>
            </div>
        </div>

        <div
            class="border-t border-zinc-200 dark:border-zinc-800 pt-8 flex flex-col md:flex-row items-center justify-between">
            <p class="text-xs text-zinc-500 dark:text-zinc-400">
                &copy; {{ date('Y') }} {!! $siteName !!}. All rights reserved.
            </p>
            <div class="mt-4 md:mt-0 text-zinc-500 text-sm">
                <!-- Fallback footer widget area if needed -->
                @php(dynamic_sidebar('sidebar-footer'))
            </div>
        </div>
    </div>
</footer>
