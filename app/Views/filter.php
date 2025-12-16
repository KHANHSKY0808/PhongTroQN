<form method="POST" action="/apply-filter">

    <h3>Lọc theo khu vực</h3>
    <select name="city">
        <option value="all">Toàn quốc</option>
    </select>

    <select name="district">
        <option value="all">Tất cả</option>
    </select>

    <select name="ward">
        <option value="all">Tất cả</option>
    </select>

    <h3>Khoảng giá</h3>
    <div class="buttons">
        <label><input type="radio" name="price" value="all" checked> Tất cả</label>
        <label><input type="radio" name="price" value="under1"> Dưới 1 triệu</label>
        <label><input type="radio" name="price" value="1to2"> 1 - 2 triệu</label>
        <label><input type="radio" name="price" value="2to3"> 2 - 3 triệu</label>
        <label><input type="radio" name="price" value="3to5"> 3 - 5 triệu</label>
        <label><input type="radio" name="price" value="5to7"> 5 - 7 triệu</label>
        <label><input type="radio" name="price" value="7to10"> 7 - 10 triệu</label>
        <label><input type="radio" name="price" value="10to15"> 10 - 15 triệu</label>
        <label><input type="radio" name="price" value="over15"> Trên 15 triệu</label>
    </div>

    <h3>Khoảng diện tích</h3>
    <div class="buttons">
        <label><input type="radio" name="area" value="all" checked> Tất cả</label>
        <label><input type="radio" name="area" value="under20"> Dưới 20m²</label>
        <label><input type="radio" name="area" value="20to30"> 20 - 30m²</label>
        <label><input type="radio" name="area" value="30to50"> 30 - 50m²</label>
        <label><input type="radio" name="area" value="50to70"> 50 - 70m²</label>
        <label><input type="radio" name="area" value="70to90"> 70 - 90m²</label>
        <label><input type="radio" name="area" value="over90"> Trên 90m²</label>
    </div>

    <button type="submit" class="apply-btn">Áp dụng</button>
</form>
