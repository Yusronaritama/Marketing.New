{{-- MODAL TAMBAH GELOMBANG --}}
<div id="addGelombangModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 999999; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: white; width: 100%; max-width: 500px; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
        <div style="padding: 1.5rem; border-bottom: 1px solid #E8ECF1; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-weight: 700;">Tambah Gelombang</h3>
            <button type="button" onclick="closeModal('addGelombangModal')" style="background: none; border: none; font-size: 1.5rem; color: #8492A6; cursor: pointer;">&times;</button>
        </div>
        <form action="{{ route('admin.mastergelombang.store') }}" method="POST" style="margin: 0;">
            @csrf
            <div style="padding: 20px;">
                <div style="margin-bottom: 15px;">
                    <label style="font-size: 0.8rem; font-weight: 600; color: #8492A6; display: block; margin-bottom: 5px;">NAMA GELOMBANG</label>
                    <input type="text" name="nama_gelombang" required placeholder="Contoh: Gelombang 6" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="font-size: 0.8rem; font-weight: 600; color: #8492A6; display: block; margin-bottom: 5px;">ANGKA GELOMBANG</label>
                    <input type="number" name="angka_gelombang" required placeholder="Contoh: 6" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                    <small style="color: #FF5B5B;">*Angka ini yang akan disimpan ke database mahasiswa.</small>
                </div>
                <div style="display: flex; gap: 15px;">
                    <div style="width: 50%;">
                        <label style="font-size: 0.8rem; font-weight: 600; color: #8492A6; display: block; margin-bottom: 5px;">TGL MULAI</label>
                        <input type="date" name="tanggal_mulai" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                    </div>
                    <div style="width: 50%;">
                        <label style="font-size: 0.8rem; font-weight: 600; color: #8492A6; display: block; margin-bottom: 5px;">TGL SELESAI</label>
                        <input type="date" name="tanggal_selesai" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                    </div>
                </div>
            </div>
            <div style="padding: 1rem 1.5rem; background: #F8FAFC; border-top: 1px solid #E8ECF1; text-align: right;">
                <button type="button" onclick="closeModal('addGelombangModal')" style="padding: 10px 20px; background: white; border: 1px solid #E8ECF1; border-radius: 8px; cursor: pointer;">Batal</button>
                <button type="submit" style="padding: 10px 20px; background: linear-gradient(135deg, #5B7FFF 0%, #667EEA 100%); color: white; border: none; border-radius: 8px; cursor: pointer;">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- MODAL EDIT GELOMBANG --}}
<div id="editGelombangModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 999999; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: white; width: 100%; max-width: 500px; border-radius: 16px; overflow: hidden; box-shadow: 0 10px 40px rgba(0,0,0,0.2);">
        <div style="padding: 1.5rem; border-bottom: 1px solid #E8ECF1; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="margin: 0; font-weight: 700;">Edit Gelombang</h3>
            <button type="button" onclick="closeModal('editGelombangModal')" style="background: none; border: none; font-size: 1.5rem; color: #8492A6; cursor: pointer;">&times;</button>
        </div>
        <form id="edit_form" method="POST" style="margin: 0;">
            @csrf
            @method('PUT')
            <div style="padding: 20px;">
                <div style="margin-bottom: 15px;">
                    <label style="font-size: 0.8rem; font-weight: 600; color: #8492A6; display: block; margin-bottom: 5px;">NAMA GELOMBANG</label>
                    <input type="text" name="nama_gelombang" id="edit_nama_gelombang" required style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="font-size: 0.8rem; font-weight: 600; color: #8492A6; display: block; margin-bottom: 5px;">ANGKA GELOMBANG</label>
                    <input type="number" name="angka_gelombang" id="edit_angka_gelombang" required style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                </div>
                <div style="display: flex; gap: 15px;">
                    <div style="width: 50%;">
                        <label style="font-size: 0.8rem; font-weight: 600; color: #8492A6; display: block; margin-bottom: 5px;">TGL MULAI</label>
                        <input type="date" name="tanggal_mulai" id="edit_tanggal_mulai" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                    </div>
                    <div style="width: 50%;">
                        <label style="font-size: 0.8rem; font-weight: 600; color: #8492A6; display: block; margin-bottom: 5px;">TGL SELESAI</label>
                        <input type="date" name="tanggal_selesai" id="edit_tanggal_selesai" style="width: 100%; border: 1px solid #E8ECF1; border-radius: 8px; padding: 10px;">
                    </div>
                </div>
            </div>
            <div style="padding: 1rem 1.5rem; background: #F8FAFC; border-top: 1px solid #E8ECF1; text-align: right;">
                <button type="button" onclick="closeModal('editGelombangModal')" style="padding: 10px 20px; background: white; border: 1px solid #E8ECF1; border-radius: 8px; cursor: pointer;">Batal</button>
                <button type="submit" style="padding: 10px 20px; background: linear-gradient(135deg, #5B7FFF 0%, #667EEA 100%); color: white; border: none; border-radius: 8px; cursor: pointer;">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>