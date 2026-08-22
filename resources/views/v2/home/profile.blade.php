@extends('v2.layouts.app')

@section('title', 'حسابي - خيرات الأنعام')

@section('content')
<div class="v2-container" style="padding: 40px 15px;">
    
    <div style="display: flex; gap: 30px; flex-wrap: wrap;">
        
        <!-- Sidebar Navigation -->
        <aside style="width: 250px; flex-shrink: 0;">
            <div style="background: var(--white); border-radius: 12px; box-shadow: 0 2px 15px rgba(0,0,0,0.03); overflow: hidden;">
                <!-- Profile Header -->
                <div style="padding: 30px 20px; text-align: center; border-bottom: 1px solid var(--border-color); background: #fafafa;">
                    <div style="width: 80px; height: 80px; background: var(--primary-color); color: #fff; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px; margin: 0 auto 15px; font-weight: 800;">
                        {{ substr(auth()->user()->name ?? 'User', 0, 1) }}
                    </div>
                    <h3 style="margin: 0 0 5px; font-size: 18px;">{{ auth()->user()->name ?? 'اسم المستخدم' }}</h3>
                    <p style="margin: 0; color: var(--text-light); font-size: 14px;">{{ auth()->user()->email ?? 'user@example.com' }}</p>
                </div>
                
                <!-- Links -->
                <ul style="list-style: none; padding: 0; margin: 0;">
                    <li>
                        <a href="#" style="display: block; padding: 15px 20px; color: var(--primary-color); background: rgba(227, 38, 54, 0.05); font-weight: 600; text-decoration: none; border-right: 3px solid var(--primary-color);">
                            <i class="fas fa-user" style="width: 25px;"></i> تفاصيل الحساب
                        </a>
                    </li>
                    <li style="border-top: 1px solid var(--border-color);">
                        <a href="#" style="display: block; padding: 15px 20px; color: var(--text-color); font-weight: 600; text-decoration: none; transition: 0.2s;">
                            <i class="fas fa-shopping-bag" style="width: 25px;"></i> طلباتي
                        </a>
                    </li>
                    <li style="border-top: 1px solid var(--border-color);">
                        <a href="#" style="display: block; padding: 15px 20px; color: var(--text-color); font-weight: 600; text-decoration: none; transition: 0.2s;">
                            <i class="fas fa-map-marker-alt" style="width: 25px;"></i> العناوين
                        </a>
                    </li>
                    <li style="border-top: 1px solid var(--border-color);">
                        <a href="#" style="display: block; padding: 15px 20px; color: #e74c3c; font-weight: 600; text-decoration: none; transition: 0.2s;">
                            <i class="fas fa-sign-out-alt" style="width: 25px;"></i> تسجيل الخروج
                        </a>
                    </li>
                </ul>
            </div>
        </aside>

        <!-- Main Content Area (Account Details) -->
        <main style="flex: 1; min-width: 300px;">
            <div style="background: var(--white); border-radius: 12px; box-shadow: 0 2px 15px rgba(0,0,0,0.03); padding: 30px;">
                <h2 style="margin-top: 0; font-size: 22px; border-bottom: 1px solid var(--border-color); padding-bottom: 15px; margin-bottom: 30px;">تفاصيل الحساب</h2>
                
                <form action="#" method="POST">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                        <div>
                            <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size:14px; color: var(--text-light);">الاسم الكامل</label>
                            <input type="text" value="{{ auth()->user()->name ?? '' }}" required style="width: 100%; padding: 12px 15px; border: 1px solid var(--border-color); border-radius: 8px; font-family: inherit; font-size: 14px; box-sizing: border-box;">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size:14px; color: var(--text-light);">البريد الإلكتروني</label>
                            <input type="email" value="{{ auth()->user()->email ?? '' }}" required style="width: 100%; padding: 12px 15px; border: 1px solid var(--border-color); border-radius: 8px; font-family: inherit; font-size: 14px; box-sizing: border-box; background: #f9f9f9;" readonly>
                        </div>
                    </div>
                    
                    <div style="margin-bottom: 30px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size:14px; color: var(--text-light);">رقم الهاتف</label>
                        <input type="tel" value="{{ auth()->user()->Number ?? '' }}" required style="width: 100%; padding: 12px 15px; border: 1px solid var(--border-color); border-radius: 8px; font-family: inherit; font-size: 14px; box-sizing: border-box; text-align:right;" dir="ltr">
                    </div>

                    <h3 style="font-size: 18px; margin-bottom: 20px;">تغيير كلمة المرور</h3>
                    
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size:14px; color: var(--text-light);">كلمة المرور الحالية</label>
                        <input type="password" style="width: 100%; padding: 12px 15px; border: 1px solid var(--border-color); border-radius: 8px; font-family: inherit; font-size: 14px; box-sizing: border-box;">
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
                        <div>
                            <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size:14px; color: var(--text-light);">كلمة المرور الجديدة</label>
                            <input type="password" style="width: 100%; padding: 12px 15px; border: 1px solid var(--border-color); border-radius: 8px; font-family: inherit; font-size: 14px; box-sizing: border-box;">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 8px; font-weight: 600; font-size:14px; color: var(--text-light);">تأكيد كلمة المرور</label>
                            <input type="password" style="width: 100%; padding: 12px 15px; border: 1px solid var(--border-color); border-radius: 8px; font-family: inherit; font-size: 14px; box-sizing: border-box;">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-primary" style="padding: 12px 30px; font-size: 16px;">حفظ التغييرات</button>
                </form>
            </div>
        </main>
    </div>
</div>
@endsection
