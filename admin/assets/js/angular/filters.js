app.filter("distance", function()
{
	return function(x)
	{
		if(x == null || x == -1)
		{
			return "ไม่ระบุ";
		}
		else if(x < 1000)
		{
			return Math.round(x) + " ม.";
		}
		else
		{
			return Math.round(x / 10) / 100 + " กม.";
		}
	};
});

app.filter("relativeTime", function()
{
	return function(x)
	{		
		x = Math.round(Date.now() / 1000) - x;
		
		if(x > 0)
		{
			if(x < 60)
			{
				return x + " วินาทีที่แล้ว";
			}
			else if(x < 3600)
			{
				return Math.round(x / 60) + " นาทีที่แล้ว";
			}
			else if(x < 86400)
			{
				return Math.round(x / 3600) + " ชั่วโมงที่แล้ว";
			}
			else
			{
				return Math.round(x / 86400) + " วันที่แล้ว";
			}
		}
		else if(x == 0)
		{
			 return "ตอนนี้";
		}
		else
		{
			x *= -1;			
			
			if(x < 60)
			{
				return "อีก " + x + " วินาที";
			}
			else if(x < 3600)
			{
				return "อีก " + Math.round(x / 60) + " นาที";
			}
			else if(x < 86400)
			{
				return "อีก " + Math.round(x / 3600) + " ชั่วโมง";
			}
			else
			{
				return "อีก " + Math.round(x / 86400) + " วัน";
			}
		}
		
		return "";
	};
});

app.filter("isavailable", function()
{
	return function(x)
	{
		if(x == 1)
		{
			return "ปกติ";
		}
		else
		{
			return "ไม่เดินรถ";
		}
	};
});

app.filter("isstop", function()
{
	return function(x)
	{
		if(x == 1)
		{
			return "ป้ายหยุด";
		}
		else
		{
			return "สถานที่";
		}
	};
});